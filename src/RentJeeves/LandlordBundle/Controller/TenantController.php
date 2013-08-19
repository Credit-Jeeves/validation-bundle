<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\LandlordBundle\Form\InviteTenantContractType;

class TenantController extends Controller
{
    /**
     * @Route(
     *     "/tenant/new",
     *     name="landlord_tenant_new",
     *     options={"expose"=true}
     * )
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        return array(
            'nGroups' => $groups->count(),
            'Group' => $this->getCurrentGroup(),
        );
    }

    /**
     * @Route(
     *     "/tenant/invite/save",
     *     name="landlord_invite_save",
     *     options={"expose"=true}
     * )
     * @Template()
     */
    public function saveInviteTenantAction()
    {
        $form = $this->createForm(
             new InviteTenantContractType($this->getUser())
        );

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                echo "form is valid";
                exit;
            }
        }
        var_dump($form->getErrors());
        //print_r($form->getErrorsAsString());
        //print_r(get_class_methods($form));
        exit;
        echo "form not valid";
        exit;
    }

private function getErrorMessages(\Symfony\Component\Form\Form $form) {
    $errors = array();

    if ($form->hasChildren()) {
        foreach ($form->getChildren() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
    } else {
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }   
    }

    return $errors;
}
}
