<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\PublicBundle\Form\InviteLandlordType;
use RentJeeves\PublicBundle\Services\ReminderInvite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\LandlordAddressType;
use Symfony\Component\HttpFoundation\Request;

class LandlordController extends Controller
{
    use PaymentProcess;

    /**
     * @Route("/landlord/register/", name="landlord_register", options={"expose"=true})
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

            // Init DepositAccount before redirecting to dashboard
            $depositAccount = new DepositAccount($landlord->getCurrentGroup());
            $depositAccount->setHolding($depositAccount->getGroup()->getHolding());
            $em = $this->getDoctrine()->getManager();
            $em->persist($depositAccount);
            $em->flush();

            return $this->get('common.login.manager')->loginAndRedirect(
                $landlord,
                $this->generateUrl('landlord_tenants')
            );
        }

        return array(
            'form'          => $form->createView(),
            'propertyName'  => $request->get('searsh-field'),
        );
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
