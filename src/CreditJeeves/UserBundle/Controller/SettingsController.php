<?php
namespace CreditJeeves\UserBundle\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;
use CreditJeeves\ApplicantBundle\Form\Type\ContactType;
use CreditJeeves\ApplicantBundle\Form\Type\NotificationType;
use CreditJeeves\ApplicantBundle\Form\Type\RemoveType;
use CreditJeeves\CoreBundle\Controller\ApplicantController;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SettingsController extends Controller
{
    /**
     * @Route("/password", name="user_password")
     * @Route("/landlord/password", name="landlord_password")
     * @Route("/profile/")
     * @Template()
     */
    public function passwordAction()
    {
        $request = $this->get('request');
        /** @var \CreditJeeves\DataBundle\Entity\User $User */
        $User = $this->getUser();
        $sOldPassword = $User->getPassword();
        $sEmail = $User->getEmail();
        $form = $this->createForm(new PasswordType(), $User);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $User = $form->getData();
                $reEnteredPassword = $this->container->get('user.security.encoder.digest')
                    ->encodePassword($User->getPassword(), $User->getSalt());
                if ($sOldPassword == $reEnteredPassword) {
                    $aForm = $request->request->get($form->getName());
                    $sNewPassword = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($aForm['password_new']['Password'], $User->getSalt());
                    $User->setPassword($sNewPassword);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($User);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
                }
            }
        }

        return array(
            'sEmail' => $sEmail,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/contact", name="user_contact")
     * @Template()
     */
    public function contactAction()
    {
        $request = $this->get('request');
        $cjUser = $this->getUser();
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

        return array(
            'sEmail' => $sEmail,
            'form' => $form->createView()
        );

    }

    /**
     * @Route("/email", name="user_email")
     * @Template()
     */
    public function emailAction()
    {
        $request = $this->get('request');
        $cjUser = $this->getUser();
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

        return array(
            'sEmail' => $sEmail,
            'form' => $form->createView()
        );

    }

    /**
     * @Route("/remove", name="user_remove")
     * @Template()
     */
    public function removeAction()
    {
        $request = $this->get('request');
        /** @var User $User */
        $User = $this->getUser();
        $sEmail = $User->getEmail();
        $sPassword = $User->getPassword();
        $form = $this->createForm(new RemoveType(), $User);
        if ($request->getMethod() == 'POST') {
            $newUser = $User->getUserToRemove();
            $form->bind($request);
            if ($form->isValid()) {
                $reEnteredPassword = $this->container->get('user.security.encoder.digest')
                    ->encodePassword($User->getPassword(), $User->getSalt());
                if ($sPassword == $reEnteredPassword) {
                    $em = $this->getDoctrine()->getManager();
                    try {
                        $em->getConnection()->beginTransaction();
                        $em->remove($User);
                        $em->flush();
                        $newUser->setLastLogin(new \DateTime());
                        $em->persist($newUser);
                        $em->flush();
                        $em->getConnection()->commit();
                        $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
                    } catch (\Exception $e) {
                        $em->getConnection()->rollback();
                        $em->close();
                        throw $e;
                    }
                    $this->get('request')->getSession()->invalidate();
                    // Commented for develop
                    return $this->redirect($this->generateUrl('fos_user_security_login'));
                } else {
                    $this->get('session')->getFlashBag()->add('notice', 'Incorrect Password');
                }
            }
        }
        return array(
            'sEmail' => $sEmail,
            'form' => $form->createView()
        );
    }
}
