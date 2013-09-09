<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\DataBundle\Entity\Property;
use Doctrine\DBAL\DBALException;

class PropertyController extends Controller
{
    /**
     * @Route("/property/new", name="landlord_property_new", options={"expose"=true})
     * @Template()
     */
    public function newAction()
    {
        $groups = $this->getGroups();
        return array(
                'nGroups'   => $groups->count(),
                'Group'     => $this->get('core.session.landlord')->getGroup(),
        );
    }
}
