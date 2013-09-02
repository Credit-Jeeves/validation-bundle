<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;
use RentJeeves\LandlordBundle\Form\AccountInfoType;
use RentJeeves\LandlordBundle\Form\DepositType;

class SettingsController extends Controller
{

    /**
     * @Route("/account/edit", name="landlord_edit_profile")
     * @Template()
     */
    public function editProfileAction()
    {
        $landlord = $this->getUser();

        $form = $this->createForm(
            new AccountInfoType(),
            $landlord
        );
        $request = $this->get('request');

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $address = $form->getData()->getAddress();

                $em->persist($address);
                $em->persist($landlord);
                $em->flush();
            }
        }
        return array(
            'form'    => $form->createView(),
        );
    }


    /**
     * @Route("/settings/deposit", name="settings_deposit")
     * @Template()
     */
    public function settingsDepositAction()
    {
        return array(
        );
    }
}
