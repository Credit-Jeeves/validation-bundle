<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
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
        }

        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') && $group) {
            $object->addPropertyGroup($group);
        }
        $em->persist($object);
        $em->flush();

        try {
            if ($group) {
                $google = $this->container->get('google');
                $google->savePlace($object);
                $group->addGroupProperty($object);
            }
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
        $request = $this->getRequest();
        $data = $request->request->all('group_id');
        $this->get("core.session.landlord")->setGroupId($data['group_id']);
        return new JsonResponse($data);
    }

    /**
     * @Route(
     *     "/property/list",
     *     name="landlord_properties_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function getPropertiesList()
    {
        $request = $this->getRequest();
        $page = $request->request->all('data');
        $page = $page['data'];
        $data = array('properties' => array(), 'total' => 0, 'pagination' => array());
        
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Property');
        $total = $repo->countProperties($group);
        $total = count($total);
        $data['total'] = $total;
        if ($total) {
            $items = array();
            $properties = $repo->getPropetiesPage($group, $page['page'], $page['limit']);
            foreach ($properties as $property) {
                $item = $property->getItem($group);
                $items[] = $item;
            }
        }
        $data['properties'] = $items;
        $data['pagination'] = $this->propertiesPagination($total, $page['limit']);
        return new JsonResponse($data);
    }

    private function propertiesPagination($total, $limit)
    {
        $result = array();
        $pages = ceil($total / $limit);
        if ($pages < 2) {
            return $result;
        }
        for ($i = 0; $i < $pages; $i++) {
            $result[] = $i + 1;
        }
        return $result;
    }

    /**
     * @Route(
     *     "/property/delete",
     *     name="landlord_property_delete",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function deleteProperty()
    {
        $request = $this->getRequest();
        $data = $request->request->all('property_id');
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($data['property_id']);
        $user = $this->getUser();
        $holding = $user->getHolding();
        $group = $this->getCurrentGroup();
        $em = $this->getDoctrine()->getManager();
        $records = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->getUnits($property, $holding, $group);
        foreach ($records as $entity) {
            $em->remove($entity);
            $em->flush();
        }
        $group->removeGroupProperty($property);
        $em->persist($group);
        $em->flush();
        return new JsonResponse(array());
    }

    /**
     * @Route(
     *     "/unit/list",
     *     name="landlord_units_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function getUnitsList()
    {
        $user = $this->getUser();
        $holding = $user->getHolding();
        $group = $this->getCurrentGroup();
        $request = $this->getRequest();
        $data = $request->request->all('property_id');
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($data['property_id']);
        $units = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->getUnitsArray($property, $holding, $group);
        return new JsonResponse($units);
    }

    /**
     * @Route(
     *     "/unit/save",
     *     name="landlord_units_save",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function saveUnitsList()
    {
        $data = array();
        $user = $this->getUser();
        $holding = $user->getHolding();
        $group = $this->getCurrentGroup();
        $request = $this->getRequest();
        $data = $request->request->all('units');
        $property = $request->request->all('property');
        $parent = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($property['property_id']);
        if (empty($parent)) {
            return new JsonResponse($data);
        }
        $units = $data['units'];
        $unitKeys = array();
        foreach ($units as $key => $unit) {
            if (empty($unit['id']) & !empty($unit['name'])) {
                continue;
            } else {
                $unitKeys[$unit['id']] = $key;
            }
        }
        ksort($unitKeys);
        $records = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->getUnits($parent, $holding, $group);
        $em = $this->getDoctrine()->getManager();
        foreach ($records as $entity) {
            if (in_array($entity->getId(), array_keys($unitKeys))) {
                $key = $unitKeys[$entity->getId()];
                if (!empty($units[$key]['name'])) {
                    if ($units[$key]['name'] != $entity->getName()) {
                        $entity->setName($units[$key]['name']);
                        $em->persist($entity);
                        $em->flush();
                    }
                } else {
                    $em->remove($entity);
                    $em->flush();
                }
                unset($unitKeys[$key]);
            } else {
                $em->remove($entity);
                $em->flush();
            }
            
        }
        foreach ($units as $unit) {
            if (empty($unit['id']) & !empty($unit['name'])) {
                $entity = new Unit();
                $entity->setProperty($parent);
                $entity->setHolding($holding);
                $entity->setGroup($group);
                $entity->setName($unit['name']);
                $em->persist($entity);
                $em->flush();
            }
        }
        $data = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->getUnitsArray($parent, $holding, $group);
        return new JsonResponse($data);
    }
}
