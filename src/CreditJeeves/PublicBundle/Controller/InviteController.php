<?php

namespace CreditJeeves\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\UserNewType;
use CreditJeeves\ApplicantBundle\Form\Type\UserInvitePasswordType;

/**
 * 
 * @Route("/invite")
 *
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
        $isFullForm = true;
        $request = $this->get('request');
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
                if (!empty($sCurrentDob)) {
                    if ($sCurrentDob == $sFormDob) {
                        $User->setInviteCode(null);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($User);
                        $em->flush();
                        return new RedirectResponse($this->get('router')->generate('applicant_homepage'));
                    }
                } else {
                    $User->setInviteCode(null);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($User);
                    $em->flush();
                    return new RedirectResponse($this->get('router')->generate('applicant_homepage'));
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
}
