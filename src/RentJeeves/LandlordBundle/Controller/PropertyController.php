<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\DataBundle\Entity\Property;

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

    /**
     * @Route(
     *  "/property/add",
     *  name="landlord_property_add",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"},
     *  options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @return array
     */
    public function addAction()
    {
        $property = array();
        $request = $this->getRequest();
        $data = $request->request->all('address');
        $data = json_decode($data['data'], true);
        $object = new Property();
        $property = $object->parseGoogleAddress($data);
        $object = $this->getDoctrine()->getRepository('RjDataBundle:Property')->findBy($property);
        if (empty($object)) {
            $object = new Property();
            $property += $object->parseGoogleLocation($data);
            $object->fillPropertyData($property);
            $em = $this->getDoctrine()->getManager();
            $em->persist($object);
            $em->flush();
        }
        return new JsonResponse($object->getId());
    }
}
