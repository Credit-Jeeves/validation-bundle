<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\LandlordBundle\Registration\MerchantAccountModel;
use RentJeeves\LandlordBundle\Registration\SAMLEnvelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\LandlordAddressType;
use RentJeeves\PublicBundle\Form\LandlordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CreditJeeves\DataBundle\Enum\Grouptype;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as UrlGenerator;

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
            new LandlordAddressType()
        );

        $form->handleRequest($request);
        if ($form->isValid()) {

            $formData = $request->request->get($form->getName());
            $landlord = $this->get('landlord.registration')->register($form, $formData);

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
                $this->generateUrl(
                    'landlord_success_registration', array('userId' => $landlord->getId()), UrlGenerator::ABSOLUTE_URL
                ),
                $this->generateUrl(
                    'landlord_error_registration', array('userId' => $landlord->getId()), UrlGenerator::ABSOLUTE_URL
                )
            );
            $signedSaml = $this->get('signature.manager')->sign($saml->getAssertionResponse());

            return $this->render(
                'RjPublicBundle:Landlord:redirectHeartland.html.twig',
                array(
                    'saml' => $saml->encodeAssertionResponse($signedSaml),
                    'url' => $saml::ONLINE_BOARDING,
                    'samlData' => $signedSaml->saveXML($signedSaml->documentElement),
                    'portalData' => $saml->getPortalApplication()->saveXML($saml->getPortalApplication()->documentElement),
                )
            );

            // TODO: send it on success
//            $this->get('project.mailer')->sendRjCheckEmail($landlord);
//            return $this->redirect($this->generateUrl('user_new_send', array('userId' => $landlord->getId())));
        }

        return array(
            'form'          => $form->createView(),
            'propertyName'  => $request->get('searsh-field'),
        );
    }

    /**
     * @Route("/landlord/app/success/{userId}/", name="landlord_success_registration")
     * @Template()
     *
     * @return array
     */
    public function successAction($userId)
    {
        $landlord = $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Landlord')->find($userId);
        if (!$landlord) {
            throw new NotFoundHttpException('Landlord not found');
        }

        $this->get('project.mailer')->sendRjCheckEmail($landlord);

        return $this->redirect($this->generateUrl('user_new_send', array('userId' => $userId)));
    }

    /**
     * @Route("/landlord/app/error/{userId}/", name="landlord_error_registration")
     * @Template()
     *
     * @return array
     */
    public function errorAction($userId)
    {
        echo "ERROR";
        die;
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
            new LandlordType(),
            $landlord
        );
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $landlord = $form->getData();
                $aForm = $request->request->get($form->getName());

                $password = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($aForm['password']['Password'], $landlord->getSalt());

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
                return $this->login($landlord);
            }
        }

        return array(
            'code'      => $code,
            'form'      => $form->createView(),
        );
    }

    private function login($landlord)
    {
        $response = new RedirectResponse($this->generateUrl('landlord_tenants'));
        $this->container->get('fos_user.security.login_manager')->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $landlord,
            $response
        );

        $this->container->get('user.service.login_success_handler')
                ->onAuthenticationSuccess(
                    $this->container->get('request'),
                    $this->container->get('security.context')->getToken()
                );

        return $response;
    }
}
