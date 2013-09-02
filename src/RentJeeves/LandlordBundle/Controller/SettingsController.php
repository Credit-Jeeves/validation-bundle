<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;

class SettingsController extends Controller
{
    /**
     * @Route("/settings", name="landlord_settings")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/password", name="landlord_password")
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
                'form'   => $form->createView()
        );
    }
}
