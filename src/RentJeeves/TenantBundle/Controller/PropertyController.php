<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/property")
 * @author Alexandr Sharamko
 *
 */
class PropertyController extends Controller
{
    /**
     * @Route("/add", name="property_add", options={"expose"=true})
     * @Route("/add/{propertyId}", name="property_add_id", options={"expose"=true})
     * @Template()
     */
    public function addAction($propertyId = null)
    {
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            return $this->addProperty();
        }

        $em = $this->getDoctrine()->getManager();
        $google = $this->get('google');
        $property = null;
        $propertyList = array();

        if (is_null($propertyId)) {
            return array(
                'property'          => $property,
                'propertyList'      => $propertyList,
                'countPropery'      => count($propertyList),
                'propertyId'        => $propertyId
            );
        }

        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }
        
        $propertyList = $google->searchPropertyInRadius($property);
        
        if (isset($propertyList[$property->getId()])) {
            unset($propertyList[$property->getId()]);
        }

        $propertyList = array_merge(array($property), $propertyList);

        return array(
            'property'          => $property,
            'propertyList'      => $propertyList,
            'countPropery'      => count($propertyList),
            'propertyId'        => $propertyId
        );
    }

    protected function addProperty()
    {
        $request = $this->get('request');
        $propertyId = $request->request->get('propertyId');
        $em = $this->getDoctrine()->getManager();
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }
        $unitName = $request->request->get('unit'.$property->getId());
        $unitNew = $request->request->get('unitNew'.$property->getId());
        $unitSearch = null;
        if (!empty($unitName) && $unitName != 'new') {
            $unitSearch = $unitName;
        } elseif (!empty($unitNew) && $unitNew != 'none') {
            $unitSearch = $unitNew;
        }
        $tenant = $this->getUser();
        $property->createContract($em, $tenant, $unitSearch);

        return $this->redirect($this->generateUrl('tenant_homepage'), 301);
    }

    /**
     * @Route("/invite/{propertyId}", name="tenant_invite_landlord", options={"expose"=true})
     * @Template()
     */
    public function inviteLandlordAction($propertyId)
    {
        return array(
            'propertyId'          => $propertyId,
        );
    }
}
