<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PropertiesController extends Controller
{
    /**
     * @Route("/properties", name="landlord_properties")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        return array('nGroups' => $groups->count());
    }
}
