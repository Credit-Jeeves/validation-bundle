<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\DataBundle\Entity\Property;
use Doctrine\DBAL\DBALException;

class PropertyController extends Controller
{
    /**
     * @Route("/property/new", name="landlord_property_new")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        return array('nGroups' => $groups->count());
    }
}
