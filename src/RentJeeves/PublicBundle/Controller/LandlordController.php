<?php

namespace RentJeeves\PublicBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LandlordController extends Controller
{
    /**
     * @Route("/landlord/register/", name="landlord_register")
     * @Template()
     *
     * @return array
     */
    public function registerAction()
    {
        return array();
    }
}
