<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PropertiesController extends Controller
{
    /**
     * @Route(
     *     "/properties",
     *     name="landlord_properties",
     *     options={"expose"=true}
     * )
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $landlordHasProperty = $em->getRepository('RjDataBundle:Property')->landlordHasProperty($this->getUser());

        if (!$landlordHasProperty) {
            return $this->redirect($this->generateUrl("landlord_property_new"));
        }

        $groups = $this->getGroups();
        return array(
            'nGroups'   => $groups->count(),
            'Group'     => $this->get('core.session.landlord')->getGroup(),
        );
    }
}
