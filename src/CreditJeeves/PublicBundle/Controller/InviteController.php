<?php

namespace CreditJeeves\PublicBundle\Controller;

use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\GroupType;
use CreditJeeves\DataBundle\Enum\LeadStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\UserNewType;
use CreditJeeves\ApplicantBundle\Form\Type\UserInvitePasswordType;

/**
 * @Route("/invite")
 */
class InviteController extends Controller
{
    /**
     * @Route(
     *     "/{code}",
     *     name="applicant_invite"
     * )
     * @Template()
     * @param string $code
     * @return array
     */
    public function indexAction($code)
    {
        $vehicles = $this->get('data.utility.vehicle')->getVehicles();
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        $isFullForm = true;
        $request = $this->get('request');
        /** @var Applicant $User */
        $User = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' =>  $code));
        if (empty($User)) {
            $i18n = $this->get('translator');
            $this->get('session')->getFlashBag()->add('message_title', $i18n->trans('error.user.absent.title'));
            $this->get('session')->getFlashBag()->add('message_body', $i18n->trans('error.user.absent.text'));

            return new RedirectResponse($this->get('router')->generate('public_message_flash'));
        }

        $date = $User->getDateOfBirth();
        $lead = $this->getActiveLead($User);
        /** @var Group $group */
        $group = $lead->getGroup();
        $type = ($group) ? $group->getType() : null;
        $sCurrentDob = null;
        if (!empty($date)) {
            $sCurrentDob = $date->format("Y-m-d");
            $User->setDateOfBirth(null);
        }
        $sSsn = $User->getSsn();
        if ($sSsn) {
            $isFullForm = false;
            $form = $this->createForm(
                new UserInvitePasswordType(),
                $User
            );
        } else {
            $address = new Address();
            $address->setUser($User);
            $User->addAddress($address);
            $User->getDefaultAddress()->setUser($User); // TODO it can be done more clear
            $form = $this->createForm(
                new UserNewType(),
                $User,
                array(
                    'currentGroupType'  => $type,
                    'vehicles'          => $this->get('data.utility.vehicle')->getVehicles(),
                )
            );
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $User = $form->getData();
            if ($type === GroupType::VEHICLE && $isFullForm) {
                $targetNameForm = $form->get('target_name');
                $dataTarget = $targetNameForm->getData();
                $markNameId = $dataTarget['make'];
                $modelId = $dataTarget['model'];

                $mark = array_keys($vehicles);
                $models = array_values($vehicles);
                $currentModels = $models[$markNameId];
                $currentModelsName = array_keys($currentModels);
                $currentModelsLinks = array_values($currentModels);

                $lead = $this->getActiveLead($User);
                $lead->setTargetName($mark[$markNameId].' '.$currentModelsName[$modelId]);
                $lead->setTargetUrl($currentModelsLinks[$modelId]);
                $lead->setStatus(LeadStatus::ACTIVE);
                $em->persist($lead);
            }

            $User->setPassword(
                $this->container->get('user.security.encoder.digest')
                    ->encodePassword($User->getPassword(), $User->getSalt())
            );

            $sFormDob = $User->getDateOfBirth()->format("Y-m-d");
            if (empty($sCurrentDob) || $sCurrentDob == $sFormDob) {
                $User->setInviteCode(null);
                $User->setIsActive(true);
                $User->setEnabled(true);

                $em->persist($User);
                $em->flush();

                return $this->login($User);
            }
        }

        return array(
            'code'       => $code,
            'form'       => $form->createView(),
            'isFullForm' => $isFullForm,
            'sName'      => $User->getFirstName(),
            'type'       => $type,
            'vehicles'   => json_encode(array_values($vehicles)),
        );
    }

    private function login($applicant)
    {
        $response = new RedirectResponse($this->generateUrl('applicant_homepage'));
        $this->container->get('fos_user.security.login_manager')->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $applicant,
            $response
        );

        $this->container->get('user.service.login_success_handler')
        ->onAuthenticationSuccess(
            $this->container->get('request'),
            $this->container->get('security.context')->getToken()
        );

        return $response;
    }

    /**
     * @param User $user
     *
     * @return Lead|mixed
     */
    private function getActiveLead(User $user)
    {
        $nLeads = $user->getUserLeads()->count();
        if ($nLeads > 0) {
            return $user->getUserLeads()->last();
        } else {
            return new Lead();
        }
    }
}
