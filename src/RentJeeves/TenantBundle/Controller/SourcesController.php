<?php

namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SourcesController extends Controller
{
    /**
     * @Route("/sources", name="tenant_payment_sources")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
