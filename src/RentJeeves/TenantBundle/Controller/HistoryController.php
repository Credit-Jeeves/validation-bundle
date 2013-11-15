<?php

namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
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
        //For this page need show unit each was removed
        //@TODO find best way for this implementation
        $this->get('doctrine')->getFilters()->disable('softdeleteable');
        $user = $this->getUser();
        return array(
            'user' => $user,
        );
    }
}
