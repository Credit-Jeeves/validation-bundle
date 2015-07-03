<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
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
        if (null === $currentGroup = $this->getCurrentGroup()) {
            throw new \LogicException(sprintf('Group not found for Landlord#%d', $this->getUser()->getId()));
        }

        return [
            'nGroups' => $groups->count(),
            'Group' => $currentGroup,
        ];
    }
}
