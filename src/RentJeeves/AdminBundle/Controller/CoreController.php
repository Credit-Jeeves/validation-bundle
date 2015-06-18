<?php

namespace RentJeeves\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CoreController as BaseController;

class CoreController extends BaseController
{
    /**
     * @Route("dashboard", name="sonata_admin_dashboard")
     * @Template()
     *
     * @return array
     */
    public function dashboardAction()
    {
        $request = $this->getRequest();
        $request->getSession()->set('contract_id', null);
        $request->getSession()->set('user_id', null);
        $request->getSession()->set('holding_id', null);
        $request->getSession()->set('landlord_id', null);
        $request->getSession()->set('group_id', null);
        $request->getSession()->set('property_id', null);

        return parent::dashboardAction();
    }
}
