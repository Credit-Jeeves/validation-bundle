<?php

namespace CreditJeeves\ApplicantBundle\Controller;

use CreditJeeves\UserBundle\Form\Type\PasswordType;

use CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SettingsController extends Controller
{
    /**
     * @Route("/password", name="applicant_password")
     * @Template()
     */
    public function passwordAction()
    {
        $request = $this->get('request');
        $sRouteName = $request->get('_route');
        $cjUser = $this->get('core.session.applicant')->getUser();
        $sOldPassword = $cjUser->getPassword();
        $sEmail = $cjUser->getEmail();
        $form = $this->createForm(new PasswordType(), $cjUser);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $cjUser = $form->getData();
                if ($sOldPassword == $cjUser->getPassword()) {
                    $sNewPassword = $cjUser->getNewPassword();
                    $cjUser->setPassword($sNewPassword);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($cjUser);
                    $em->flush();
                }
                return $this->redirect($this->generateUrl('applicant_password'));
            }
        }
        return $this->render(
                'ApplicantBundle:Settings:password.html.twig',
                array(
                        'sEmail' => $sEmail,
                        'sRouteName' => $sRouteName,
                        'form'    => $form->createView()
                )
        );
        
    }

    /**
     * @Route("/password", name="applicant_contact")
     * @Template()
     */
    public function contactAction()
    {
        $request = $this->get('request');
        $sRouteName = $request->get('_route');
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $sEmail = $cjUser->getEmail();
        
        return $this->render(
                'ApplicantBundle:Settings:contact.html.twig',
                array(
                        'sEmail' => $sEmail,
                        'sRouteName' => $sRouteName,
//                        'form'    => $form->createView()
                )
        );
        
    }

    /**
     * @Route("/password", name="applicant_email")
     * @Template()
     */
    public function emailAction()
    {
        $request = $this->get('request');
        $sRouteName = $request->get('_route');
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $sEmail = $cjUser->getEmail();
        
        return $this->render(
                'ApplicantBundle:Settings:email.html.twig',
                array(
                        'sEmail' => $sEmail,
                        'sRouteName' => $sRouteName,
//                        'form'    => $form->createView()
                )
        );
        
    }

    /**
     * @Route("/password", name="applicant_remove")
     * @Template()
     */
    public function removeAction()
    {
        $request = $this->get('request');
        $sRouteName = $request->get('_route');
        $cjUser = $this->get('security.context')->getToken()->getUser();
        $sEmail = $cjUser->getEmail();
        
        return $this->render(
                'ApplicantBundle:Settings:remove.html.twig',
                array(
                        'sEmail' => $sEmail,
                        'sRouteName' => $sRouteName,
 //                       'form'    => $form->createView()
                )
        );
        
    }
}
