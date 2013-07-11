<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\DataBundle\Entity\Property;
use Doctrine\DBAL\DBALException;

/**
 * 
 * @Route("/ajax")
 *
 */
class AjaxController extends Controller
{
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
        $object = $this->getDoctrine()->getRepository('RjDataBundle:Property')->findOneBy($property);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $group = $this->get("core.session.landlord")->getGroup();
        if (empty($object)) {
            $object = new Property();
            $property += $object->parseGoogleLocation($data);
            $object->fillPropertyData($property);
            $object->addPropertyGroup($group);
            $em->persist($object);
            $em->flush();
        }
        try {
            $group->addGroupProperty($object);
            $em->flush();
        } catch (DBALException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
        }
        return new JsonResponse($object->getId());
    }

    /**
     * @Route(
     *     "/group/set",
     *     name="landlord_group_set",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function setGroup()
    {
        
    }
}
