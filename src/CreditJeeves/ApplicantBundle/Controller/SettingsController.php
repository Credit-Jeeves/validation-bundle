<?php
namespace CreditJeeves\ApplicantBundle\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;
use CreditJeeves\ApplicantBundle\Form\Type\ContactType;
use CreditJeeves\ApplicantBundle\Form\Type\NotificationType;
use CreditJeeves\ApplicantBundle\Form\Type\RemoveType;

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
            }
        }

        return $this->render(
            'ApplicantBundle:Settings:password.html.twig',
            array(
                'sEmail' => $sEmail,
                'form' => $form->createView()
            )
        );

    }

    /**
     * @Route("/contact", name="applicant_contact")
     * @Template()
     */
    public function contactAction()
    {
        $request = $this->get('request');
        $cjUser = $this->get('core.session.applicant')->getUser();
        $sEmail = $cjUser->getEmail();
        $form = $this->createForm(new ContactType(), $cjUser);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cjUser);
                $em->flush();
                $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
            }
        }

        return $this->render(
            'ApplicantBundle:Settings:contact.html.twig',
            array(
                'sEmail' => $sEmail,
                'form' => $form->createView()
            )
        );

    }

    /**
     * @Route("/email", name="applicant_email")
     * @Template()
     */
    public function emailAction()
    {
        $request = $this->get('request');
        $cjUser = $this->get('core.session.applicant')->getUser();
        $sEmail = $cjUser->getEmail();
        $form = $this->createForm(new NotificationType(), $cjUser);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cjUser);
                $em->flush();
                $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
            }
        }

        return $this->render(
            'ApplicantBundle:Settings:email.html.twig',
            array(
                'sEmail' => $sEmail,
                'form' => $form->createView()
            )
        );

    }

    /**
     * @Route("/remove", name="applicant_remove")
     * @Template()
     */
    public function removeAction()
    {
        $request = $this->get('request');
        $cjUser = $this->get('core.session.applicant')->getUser();
        $sEmail = $cjUser->getEmail();
        $sPassword = $cjUser->getPassword();
        $form = $this->createForm(new RemoveType(), $cjUser);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                if ($sPassword == $cjUser->getPassword()) {
                    $em = $this->getDoctrine()->getManager();
                    try {
                        $em->getConnection()->beginTransaction();
                        $em->getRepository('DataBundle:User')->removeUserData($cjUser);
                        $cjUser->removeData();
                        $em->persist($cjUser);
                        $em->flush();
                        $em->getConnection()->commit();
                        $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
                    } catch (Exception $e) {
                        $em->getConnection()->rollback();
                        $em->close();
                        throw $e;
                    }
                    // Commented for develop
                    //return $this->redirect($this->generateUrl('fos_user_security_logout'));
                } else {
                    $this->get('session')->getFlashBag()->add('notice', 'Incorrect Password');
                }
            }
        }
        return $this->render(
            'ApplicantBundle:Settings:remove.html.twig',
            array(
                'sEmail' => $sEmail,
                'form' => $form->createView()
            )
        );
    }
}
