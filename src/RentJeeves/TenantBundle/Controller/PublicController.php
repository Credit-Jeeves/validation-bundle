<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PublicController extends Controller
{
    /**
     * @Route("/public/search", name="tenant_search")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/public/check/search/{address}/{geometry}", name="tenant_search_check", options={"expose"=true})
     * @Template()
     */
    public function checkSearchAction($address, $geometry)
    {

    }
}
