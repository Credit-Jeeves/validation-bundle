<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/ajax")
 * @author alex
 *
 */
class AjaxController extends Controller
{
    /**
     * @Route("/contracts", name="tenant_contracts")
     * @Template()
     */
    public function contractsAction()
    {
        return array();
    }
}
