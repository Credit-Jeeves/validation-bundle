<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PropertyController extends Controller
{
    /**
     * @Route("/property/new", name="landlord_property_new")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
