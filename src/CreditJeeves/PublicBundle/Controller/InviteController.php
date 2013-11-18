<?php

namespace CreditJeeves\PublicBundle\Controller;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\User;
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
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        $isFullForm = true;
        $request = $this->get('request');
        /** @var User $User */
        $User = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' =>  $code));
        if (empty($User)) {
            $i18n = $this->get('translator');
            $this->get('session')->getFlashBag()->add('message_title', $i18n->trans('error.user.absent.title'));
            $this->get('session')->getFlashBag()->add('message_body', $i18n->trans('error.user.absent.text'));
            return new RedirectResponse($this->get('router')->generate('public_message_flash'));
        }


        $date = $User->getDateOfBirth();
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
                $User
            );
        }
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $User = $form->getData();
                $User->setPassword(
                    $this->container->get('user.security.encoder.digest')
                        ->encodePassword($User->getPassword(), $User->getSalt())
                );
                $sFormDob = $User->getDateOfBirth()->format("Y-m-d");
                if (empty($sCurrentDob) || $sCurrentDob == $sFormDob) {
                    $User->setInviteCode(null);
                    $User->setEnabled(true);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($User);
                    $em->flush();
                    return $this->login($User);
                }

            }
        }
        return array(
            'code' => $code,
            'form' => $form->createView(),
            'isFullForm' => $isFullForm,
            'sName' => $User->getFirstName(),
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
}
