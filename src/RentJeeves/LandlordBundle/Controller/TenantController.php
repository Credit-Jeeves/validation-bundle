<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
}
