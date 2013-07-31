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
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * 
 * @Route("/ajax")
 *
 */
class AjaxController extends Controller
{
    /* Property */

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

            if(!empty($page['sortColumn'])) {
                $isSortAsc = ($page['isSortAsc'] === 'true');
                $properties = $repo->getPropetiesPage($group, $page['page'], $page['limit'], $page['sortColumn'], $isSortAsc);
            } else {
                $properties = $repo->getPropetiesPage($group, $page['page'], $page['limit']);
            }
            
            foreach ($properties as $property) {
                $item = $property->getItem($group);
                $items[] = $item;
            }
        }
        $data['properties'] = $items;
        $data['pagination'] = $this->datagridPagination($total, $page['limit']);
        return new JsonResponse($data);
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
    public function addProperty()
    {
        $property = array();
        $itsNewProperty = false;
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
            $itsNewProperty = true;
        }

        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')
                && $group
                && $this->getUser()->getType() == UserType::LANDLORD
        ) {
            //@TODO need check, maybe this group alredy exist on this property
            $object->addPropertyGroup($group);
            $group->addGroupProperty($object);
        }
        $em->persist($object);
        $em->persist($group);
        $em->flush();

        if ($group && $this->getUser()->getType() == UserType::LANDLORD && $itsNewProperty) {
            $google = $this->container->get('google');
            $google->savePlace($object);
        }


        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($object->getId());

        $data = array(
            'hasLandlord'   => ($countGroup > 0) ? true : false,
            'property'      => array(
                    'id'        => $object->getId(),
                    'city'      => $object->getCity(),
                    'number'    => ($object->getNumber()) ? $object->getNumber() : '',
                    'street'    => $object->getStreet(),
                    'area'      => $object->getArea(),
                    'zip'       => ($object->getZip()) ? $object->getZip() : '',
                    'jb'        => $object->getJb(),
                    'kb'        => $object->getKb(),
                    'address'   => $object->getAddress(),
            ),
        );

        return new JsonResponse($data);
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
    

    /* Unit */

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
        $result = array('property' => '', 'units' => array());
        $user = $this->getUser();
        $holding = $user->getHolding();
        $group = $this->getCurrentGroup();
        $request = $this->getRequest();
        $data = $request->request->all('property_id');
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($data['property_id']);
        $result['property'] = $property->getAddress();
        $result['units'] = $this->getDoctrine()
            ->getRepository('RjDataBundle:Unit')
            ->getUnitsArray(
                $property,
                $holding,
                $group
            );
        return new JsonResponse($result);
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

    /* Tenant */

    /**
     * @Route(
     *     "/tenant/list",
     *     name="landlord_tenants_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function getTenantsList()
    {
        $items = array();
        $total = 0;
        $request = $this->getRequest();
        $page = $request->request->all('data');
        $page = $page['data'];
        $data = array('tenants' => array(), 'total' => 0, 'pagination' => array());
    
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Tenant');
        $total = $repo->countTenants($group);
        $total = count($total);
        if ($total) {
            $tenants = $repo->getTenantsPage($group, $page['page'], $page['limit']);
            foreach ($tenants as $tenant) {
                $item = $tenant->getItem();
                $items[] = $item;
            }
        }
        $data['tenants'] = $items;
        $data['total'] = $total;
        $data['pagination'] = $this->datagridPagination($total, $page['limit']);
        return new JsonResponse($data);
    }

    /* Contract */

    /**
     * @Route(
     *     "/contract/list",
     *     name="landlord_contracts_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function getContractsList()
    {
        $items = array();
        $total = 0;
        $request = $this->getRequest();
        $page = $request->request->all('data');
        $page = $page['data'];
        $data = array('contracts' => array(), 'total' => 0, 'pagination' => array());
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Contract');
        $total = $repo->countContracts($group);
        $total = count($total);
        if ($total) {
            $contracts = $repo->getContractsPage($group, $page['page'], $page['limit']);
            foreach ($contracts as $contract) {
                $item = $contract->getItem();
                $items[] = $item;
            }
        }
        $data['contracts'] = $items;
        $data['total'] = $total;
        $data['pagination'] = $this->datagridPagination($total, $page['limit']);
        return new JsonResponse($data);
    }

    /**
     * @Route(
     *     "/action/list",
     *     name="landlord_actions_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function getActionsList()
    {
        $items = array();
        $total = 0;
        $request = $this->getRequest();
        $page = $request->request->all('data');
        $page = $page['data'];
        $data = array('actions' => array(), 'total' => 0, 'pagination' => array());
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Contract');
        $total = $repo->countActionsRequired($group);
        $total = count($total);
        if ($total) {
            $contracts = $repo->getActionsRequiredPage($group, $page['page'], $page['limit']);
            foreach ($contracts as $contract) {
                $item = $contract->getItem();
                $items[] = $item;
            }
        }
        $data['actions'] = $items;
        $data['total'] = $total;
        $data['pagination'] = $this->datagridPagination($total, $page['limit']);
        return new JsonResponse($data);
    }

    /**
     * @Route(
     *     "/contract/save",
     *     name="landlord_contract_save",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function saveContract()
    {
        $request = $this->getRequest();
        $contract = $request->request->all('contract');
        $details = $contract['contract'];
        $action = 'edit';
        if (isset($details['action'])) {
            $action = $details['action'];
        }
        $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($details['id']);
        $tenant = $contract->getTenant();
        $tenant->setFirstName($details['first_name']);
        $tenant->setLastName($details['last_name']);
        $tenant->setEmail($details['email']);
        $tenant->setPhone($details['phone']);
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($details['property_id']);
        $unit = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->find($details['unit_id']);
        if (in_array($details['status'], array('approved'))) {
            $contract->setStatus($details['status']);
        }
        $contract->setRent($details['amount']);
        $contract->setDueDay($details['due_day']);
        $contract->setStartAt(new \Datetime($details['start']));
        $contract->setFinishAt(new \Datetime($details['finish']));
        $contract->setTenant($tenant);
        $contract->setProperty($property);
        $contract->setUnit($unit);
        $em = $this->getDoctrine()->getManager();
        if ($action == 'remove') {
            $em->remove($contract);
        } else {
            $em->persist($contract);
        }
        $em->flush();
        return new JsonResponse(array());
    }

    /* Service methods */

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

    private function datagridPagination($total, $limit)
    {
        $result = array();
        $pages = ceil($total / $limit);
        if ($pages < 2) {
            return $result;
        }
        $result[] = 'First';
        for ($i = 0; $i < $pages; $i++) {
            $result[] = $i + 1;
        }
        $result[] = 'Last';
        return $result;
    }
}
