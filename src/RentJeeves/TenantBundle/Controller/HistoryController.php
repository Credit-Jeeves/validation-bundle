<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HistoryController extends Controller
{
    /**
     * @Route("/history", name="tenant_payment_history")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
