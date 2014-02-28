<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\LandlordBundle\Registration\MerchantAccountModel;
use RentJeeves\LandlordBundle\Registration\SAMLEnvelope;
use RentJeeves\PublicBundle\Form\InviteLandlordType;
use RentJeeves\PublicBundle\Services\ReminderInvite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\LandlordAddressType;
use RentJeeves\PublicBundle\Form\LandlordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CreditJeeves\DataBundle\Enum\Grouptype;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as UrlGenerator;
use Exception;

class LandlordController extends Controller
{
    use PaymentProcess;

    /**
     * @Route("/landlord/register/", name="landlord_register")
     * @Template()
     *
     * @return array
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(
            new LandlordAddressType(),
            null,
            array(
                'inviteEmail' => true
            )
        );

        $form->handleRequest($request);
        if ($form->isValid()) {

            $formData = $request->request->get($form->getName());
            $landlord = $this->get('landlord.registration')->register($form, $formData);
            $this->get('project.mailer')->sendRjCheckEmail($landlord);

            // Login a user to be able to show his dashboard when he comes back from HPS
            $this->get('common.login.manager')->login($landlord);

            try {
                $this->setMerchantName($this->container->getParameter('rt_merchant_name'));
                $this->savePaymentAccount($form->get('deposit'), $landlord, $landlord->getCurrentGroup());

                $merchantAccount = new MerchantAccountModel(
                    $formData['deposit']['RoutingNumber'],
                    $formData['deposit']['AccountNumber'],
                    $formData['deposit']['ACHDepositType']
                );
                $saml = new SAMLEnvelope(
                    $landlord,
                    $merchantAccount,
                    $this->generateUrl('landlord_hps_success', array(), UrlGenerator::ABSOLUTE_URL),
                    $this->generateUrl('landlord_hps_error', array(), UrlGenerator::ABSOLUTE_URL)
                );
                $signedSaml = $this->get('signature.manager')->sign($saml->getAssertionResponse());

                // Init DepositAccount before redirecting to HPS
                $depositAccount = new DepositAccount($landlord->getCurrentGroup());
                $em = $this->getDoctrine()->getManager();
                $em->persist($depositAccount);
                $em->flush();

                return $this->render(
                    'RjPublicBundle:Landlord:redirectHeartland.html.twig',
                    array(
                        'saml' => $saml->encodeAssertionResponse($signedSaml),
                        'url' => $saml::ONLINE_BOARDING
                    )
                );
            } catch (Exception $e) {
                return $this->get('common.login.manager')->loginAndRedirect(
                    $landlord,
                    $this->generateUrl('landlord_tenants')
                );
            }
        }

        return array(
            'form'          => $form->createView(),
            'propertyName'  => $request->get('searsh-field'),
        );
    }

    /**
     * @Route("/landlord/app/success/", name="landlord_hps_success")
     *
     * @return RedirectResponse
     */
    public function successAction(Request $request)
    {
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            array(
                'group' => $currentUser->getCurrentGroup()->getId()
            )
        );

        $depositAccount->setStatus(DepositAccountStatus::HPS_SUCCESS);
        $em->flush();

        return $this->redirect($this->generateUrl('landlord_tenants'));
    }

    /**
     * @Route("/landlord/app/error/", name="landlord_hps_error")
     *
     * @return RedirectResponse
     */
    public function errorAction(Request $request)
    {
        $currentUser = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $depositAccount = $em->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            array(
                'group' => $currentUser->getActiveGroup()->getId()
            )
        );

        $depositAccount->setStatus(DepositAccountStatus::HPS_ERROR);
        $depositAccount->setMessage($request->query->get('msg', ''));
        $em->flush();

        return $this->redirect($this->generateUrl('landlord_tenants'));
    }

    /**
     * @Route("/landlord/invite/{code}", name="landlord_invite")
     * @Template()
     *
     * @return array
     */
    public function landlordInviteAction($code)
    {
        $landlord = $this->getDoctrine()->getRepository('RjDataBundle:Landlord')->findOneBy(
            array(
                'invite_code' => $code
            )
        );

        if (empty($landlord)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $form = $this->createForm(
            new InviteLandlordType(),
            array('landlord' => $landlord)
        );
        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $landlord = $form->getData()['landlord'];
            $formData = $request->request->get($form->getName());

            $password = $this->container->get('user.security.encoder.digest')
                    ->encodePassword($formData['landlord']['password']['Password'], $landlord->getSalt());

            $landlord->setPassword($password);
            $landlord->setCulture($this->container->parameters['kernel.default_locale']);
            $em = $this->getDoctrine()->getManager();
            $group = $landlord->getCurrentGroup();
            $contracts = $group->getContracts();
            if (!empty($contracts)) {
                foreach ($contracts as $contract) {
                    $tenant = $contract->getTenant();
                    $this->get('project.mailer')->sendRjLandlordComeFromInvite(
                        $tenant,
                        $landlord,
                        $contract
                    );
                }
            }

            $landlord->setInviteCode(null);
            $em->persist($landlord);
            $em->flush();

            try {
                $this->setMerchantName($this->container->getParameter('rt_merchant_name'));
                $this->savePaymentAccount($form->get('deposit'), $landlord, $group);
            } catch (Exception $e) {
                // do nothing, just prevent user from seeing broken app
            }

            return $this->get('common.login.manager')->loginAndRedirect(
                $landlord,
                $this->generateUrl('landlord_tenants')
            );
        }

        return array(
            'code' => $code,
            'form' => $form->createView(),
        );
    }


    /**
     * @Route("/landlord/invite/resend/{landlordId}", name="landlord_invite_resend")
     * @Template("RjPublicBundle:Public:resendInvite.html.twig")
     *
     */
    public function resendInviteLandlordAction($landlordId)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var $landlord Landlord
         */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->find($landlordId);
        if (empty($landlord)) {
            throw new LogicException("Landlord which such id {$landlordId} does not exist");
        }

        /**
         * @var $reminderInvite ReminderInvite
         */
        $reminderInvite = $this->get('reminder.invite');
        if (!$reminderInvite->sendLandlord($landlord)) {
            return array(
                'error' => $reminderInvite->getError()
            );
        }

        return array(
            'error' => false,
        );
    }
}
