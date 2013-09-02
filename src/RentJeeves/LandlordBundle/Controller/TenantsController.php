<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\LandlordBundle\Form\InviteTenantContractType;

class TenantsController extends Controller
{
    /**
     * @Route("/tenants", name="landlord_tenants")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        $form = $this->createForm(
            new InviteTenantContractType($this->getUser())
        );

        $data = array(
            'nGroups'   => $groups->count(),
            'Group'     => $this->getCurrentGroup(),
            'form'      => $form->createView(),
        );

        return $data;
    }
}
