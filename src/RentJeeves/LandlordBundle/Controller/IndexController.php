<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IndexController extends Controller
{
    /**
     * @Route("/", name="landlord_homepage")
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
