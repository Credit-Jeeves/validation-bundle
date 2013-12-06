<?php

namespace RentJeeves\LandlordBundle\Controller;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use Doctrine\DBAL\DBALException;
use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OperationType;

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
        $total = $repo->countProperties($group, $page['searchCollum'], $page['searchText']);
        $total = count($total);
        $data['total'] = $total;
        $items = array();
        if ($total) {
            $isSortAsc = ($page['isSortAsc'] === 'true');
            $properties = $repo->getPropetiesPage(
                $group,
                $page['page'],
                $page['limit'],
                $page['sortColumn'],
                $isSortAsc,
                $page['searchCollum'],
                $page['searchText']
            );
            
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
        $addGroup = $request->request->all('addGroup');
        $data = json_decode($data['data'], true);
        $addGroup = (!isset($data['addGroup'])
                     || (isset($data['addGroup']) && $data['addGroup'] == 1)
                    )?  true : false;
        $property = new Property();
        $propertyDataAddress = $property->parseGoogleAddress($data);
        $propertyDataLocation = $property->parseGoogleLocation($data);
        if (!isset($propertyDataAddress['number'])) {
            return new JsonResponse(
                array(
                    'status'  => 'ERROR',
                    'message' => $this->get('translator')->trans('property.number.not.exist')
                )
            );
        }
        $propertySearch = array_merge($propertyDataLocation, array('number' => $propertyDataAddress['number']));
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->findOneBy($propertySearch);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $group = $this->get("core.session.landlord")->getGroup();
        if (empty($property)) {
            $property = new Property();
            $propertyData = array_merge($propertyDataAddress, $propertyDataLocation);
            $property->fillPropertyData($propertyData);
            $itsNewProperty = true;
        }

        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')
                && $group
                && $this->getUser()->getType() == UserType::LANDLORD
                && $addGroup
                && !$group->getGroupProperties()->contains($property)
        ) {
            $property->addPropertyGroup($group);
            $group->addGroupProperty($property);
            $em->persist($group);
        }
        try {
            $em->persist($property);
            $em->flush();
        } catch (DBALException $e) {
            return new JsonResponse(
                array(
                    'status'  => 'ERROR',
                    'message' => $this->get('translator')->trans('fill.full.address')
                )
            );
        }
        if ($group && $this->getUser()->getType() == UserType::LANDLORD && $itsNewProperty) {
            $google = $this->container->get('google');
            $google->savePlace($property);
        }

        $securityContext = $this->container->get('security.context');
        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($property->getId());
        $isLogin = $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ? true : false;
        $isLandlord = false;

        if ($isLogin) {
            $isLandlord = ($this->getUser()->getType() == UserType::LANDLORD) ? true : false;
        }
        //@TODO refactor - change array to entity JSM serialisation
        $data = array(
            'status'                => 'OK',
            'hasLandlord'           => $property->hasLandlord(),
            'isLogin'               => $isLogin,
            'isLandlord'            => $isLandlord,
            'propertyDataAddress'   => $propertyDataAddress,
            'propertyDataLocation'  => $propertyDataLocation,
            'property'      => array(
                    'id'        => $property->getId(),
                    'city'      => $property->getCity(),
                    'number'    => ($property->getNumber()) ? $property->getNumber() : '',
                    'street'    => $property->getStreet(),
                    'area'      => $property->getArea(),
                    'zip'       => ($property->getZip()) ? $property->getZip() : '',
                    'jb'        => $property->getJb(),
                    'kb'        => $property->getKb(),
                    'address'   => $property->getAddress(),
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

    //@TODO find best way for this implementation
    private function checkContract($entity)
    {
        if ($entity->getContracts()->count() <= 0) {
            $this->get('doctrine')->getManager()->getFilters()->disable('softdeleteable');
        } else {
            $this->get('doctrine')->getManager()->getFilters()->enable('softdeleteable');
        }
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
        $names = array();
        $existingNames = array();
        $errorNames = array();
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
        $units = (isset($data['units']))? $data['units'] : array();
        $unitKeys = array();
        foreach ($units as $key => $unit) {
            if (empty($unit['id']) & !empty($unit['name'])) {
                continue;
            } else {
                $names[] = $unit['name'];
                $unitKeys[$unit['id']] = $key;
            }
            
        }
        ksort($unitKeys);
        $records = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->getUnits($parent, $holding, $group);
        $em = $this->getDoctrine()->getManager();

        /** @var $entity Unit */
        foreach ($records as $entity) {
            if (in_array($entity->getId(), array_keys($unitKeys)) & !in_array($entity->getName(), $existingNames)) {
                $key = $unitKeys[$entity->getId()];
                if (!empty($units[$key]['name']) & !in_array($units[$key]['name'], $existingNames)) {
                    $existingNames[] = $units[$key]['name'];
                    if ($units[$key]['name'] != $entity->getName()) {
                        $entity->setName($units[$key]['name']);
                        $em->persist($entity);
                        $em->flush();
                        
                    }
                } else {
                    $errorNames[] = $units[$key]['name'];
                    $this->checkContract($entity);
                    $em->remove($entity);
                    $em->flush();
                }
                unset($unitKeys[$key]);
            } else {
                $this->checkContract($entity);
                $em->remove($entity);
                $em->flush();
            }
            
        }
        foreach ($units as $unit) {
            if (empty($unit['id']) & !empty($unit['name']) & !in_array($unit['name'], $names)) {
                $entity = new Unit();
                $entity->setProperty($parent);
                $entity->setHolding($holding);
                $entity->setGroup($group);
                $entity->setName($unit['name']);
                $em->persist($entity);
                $em->flush();
                $names[] = $unit['name'];
            } else {
                $errorNames[] = $unit['name'];
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
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Tenant');
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
        //For this page need show unit each was removed
        //@TODO find best way for this implementation
        $this->get('doctrine')->getManager()->getFilters()->disable('softdeleteable');
        $items = array();
        $total = 0;
        $request = $this->getRequest();
        $dataRequest = $request->request->all('data')['data'];
        $data = array('contracts' => array(), 'total' => 0, 'pagination' => array());
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Contract');
        $total = $repo->countContracts($group, $dataRequest['searchCollum'], $dataRequest['searchText']);
        $total = count($total);
        $order  = ($dataRequest['isSortAsc'] === 'true')? "ASC" : "DESC";
        if ($total) {
            $contracts = $repo->getContractsPage(
                $group,
                $dataRequest['page'],
                $dataRequest['limit'],
                $dataRequest['sortColumn'],
                $order,
                $dataRequest['searchCollum'],
                $dataRequest['searchText']
            );
            foreach ($contracts as $contract) {
                $item = $contract->getItem();
                $items[] = $item;
            }
        }
        $data['contracts'] = $items;
        $data['total'] = $total;
        $data['pagination'] = $this->datagridPagination($total, $dataRequest['limit']);
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
        //For this page need show unit each was removed
        //@TODO find best way for this implementation
        $this->get('doctrine')->getManager()->getFilters()->disable('softdeleteable');
        $items = array();
        $total = 0;
        $request = $this->getRequest();
        $page = $request->request->all('data');
        $data = $page['data'];

        $sortColumn = $data['sortColumn'];
        $isSortAsc = $data['isSortAsc'];
        $searchField = $data['searchCollum'];
        $searchText = $data['searchText'];

        $sortType = ($isSortAsc == 'true')? "ASC" : "DESC";

        $result = array('actions' => array(), 'total' => 0, 'pagination' => array());
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('RjDataBundle:Contract');
        $total = $repo->countActionsRequired($group, $searchField, $searchText);
        $total = count($total);
        if ($total) {
            $contracts = $repo->getActionsRequiredPage(
                $group,
                $data['page'],
                $data['limit'],
                $sortColumn,
                $sortType,
                $searchField,
                $searchText
            );
            foreach ($contracts as $contract) {
                $contract->setStatusShowLateForce(true);
                $item = $contract->getItem();
                $items[] = $item;
            }
        }
        
        $result['actions'] = $items;
        $result['total'] = $total;
        $result['pagination'] = $this->datagridPagination($total, $data['limit']);
        
        return new JsonResponse($result);
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
        $errors = array();
        $response = array();
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contract = $request->request->all('contract');
        $details = $contract['contract'];
        $action = 'edit';
        if (isset($details['action'])) {
            $action = $details['action'];
        }
        if (empty($details['amount'])) {
            $errors[] = $translator->trans('contract.error.rent');
        }
        if (empty($details['start'])) {
            $errors[] = $translator->trans('contract.error.start');
        }
        $contract = $em->getRepository('RjDataBundle:Contract')->find($details['id']);
        $tenant = $contract->getTenant();
        $tenant->setFirstName($details['first_name']);
        $tenant->setLastName($details['last_name']);
        $tenant->setEmail($details['email']);
        $tenant->setPhone($details['phone']);
        $property = $em->getRepository('RjDataBundle:Property')->find($details['property_id']);
        $unit = $em->getRepository('RjDataBundle:Unit')->find($details['unit_id']);
        $contract->setRent($details['amount']);
        $contract->setStartAt(new \Datetime($details['start']));
        $contract->setFinishAt(new \Datetime($details['finish']));
        $contract->setTenant($tenant);
        $contract->setProperty($property);
        $contract->setUnit($unit);
        if (in_array($details['status'], array(ContractStatus::APPROVED)) & empty($errors)) {
            $contract->setStatusApproved();
            $this->get('project.mailer')->sendContractApprovedToTenant($contract);
        }

        if ($action == 'remove') {
            /**
             * This contract don't have any payment this is just contract, so we can remove it from db
             */
            $tenant = $contract->getTenant();
            $landlord = $this->getUser();
            $this->get('project.mailer')->sendRjContractRemovedFromDbByLandlord(
                $tenant,
                $landlord,
                $contract
            );
            $em->remove($contract);
        } else {
            $em->persist($contract);
        }

        $em->flush();
        if (!empty($errors) & 'edit' == $action) {
            $response['errors'] = $errors;
        }
        return new JsonResponse($response);
    }

    /**
     * @Route(
     *     "/contract/resolve",
     *     name="landlord_conflict_resolve",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function resolveContract()
    {
        $amount = null;
        $request = $this->getRequest();
        $data = $request->request->all('data');
        if (!isset($data['action'])) {
            return new JsonResponse(array());
        }
        if (isset($data['amount'])) {
            $amount = $data['amount'];
        }
        $contract = $this->get('doctrine.orm.default_entity_manager')
            ->getRepository('RjDataBundle:Contract')
            ->find($data['contract_id']);
        $tenant = $contract->getTenant();
        $action = $data['action'];
        switch ($action) {
            case Contract::RESOLVE_EMAIL:
                $this->get('project.mailer')->sendRjTenantLatePayment($tenant, $this->getUser(), $contract);
                break;
            case Contract::RESOLVE_PAID:
                $em = $this->getDoctrine()->getManager();
                // Check operations
                $operation = $contract->getOperation();
                if (empty($operation)) {
                    $operation = new Operation();
                    $operation->setType(OperationType::RENT);
                    $operation->setContract($contract);
                    $em->persist($operation);
                    $em->flush();
                }
                // Create order
                $order = new Order();
                $order->addOperation($operation);
                $order->setUser($tenant);
                $order->setAmount($contract->getRent());
                $order->setStatus(OrderStatus::COMPLETE);
                $order->setType(OrderType::CASH);
                $em->persist($order);
                $em->flush();
                // Change paid to date
                $contract->shiftPaidTo($amount);
                $contract->setStatus(ContractStatus::CURRENT);
                $em->persist($contract);
                $em->flush();
                break;
            case Contract::RESOLVE_UNPAID:
                // @TODO Here will be report to Experian
                break;
        }
        return new JsonResponse(array());
    }

    /* Payments */

    /**
     * @Route(
     *     "/payment/list",
     *     name="landlord_payments_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     */
    public function getPaymentsList()
    {
        // Show all unit, even it removed
        //@TODO find best way for this implementation
        $this->get('doctrine')->getManager()->getFilters()->disable('softdeleteable');
        $items = array();
        $total = 0;
        $request = $this->getRequest();
        $page = $request->request->all('data');
        $data = $page['data'];
        $sortColumn = $data['sortColumn'];
        $isSortAsc = $data['isSortAsc'];
        $searchCollum = $data['searchCollum'];
        $searchText = $data['searchText'];

        $sortType = ($isSortAsc == 'true')? "ASC" : "DESC";

        $result = array();
        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Order');
        $total = $repo->countOrders($group, $searchCollum, $searchText);
        $total = count($total);

        if ($total) {
            $orders = $repo->getOrdersPage(
                $group,
                $data['page'],
                $data['limit'],
                $sortColumn,
                $sortType,
                $searchCollum,
                $searchText
            );
            foreach ($orders as $order) {
                $item = $order->getItem();
                $items[] = $item;
            }
        }

        $result['payments'] = $items;
        $result['total'] = $total;
        $result['pagination'] = $this->datagridPagination($total, $data['limit']);
        $result['sort'] = $sortType;

        return new JsonResponse($result);
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


    /**
     * @Route(
     *     "/check/email/tenant",
     *     name="landlord_check_email",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function checkEmail()
    {
        $request = $this->get('request');
        $email = $request->request->get('email');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DataBundle:User')->findOneBy(
            array('email' => $email)
        );

        $data = array(
            'userExist' => (!empty($user))? true : false,
            'isTenant'  => (!empty($user) && $user->getType() === UserType::TETNANT)? true : false,
        );

        return new JsonResponse($data);
    }

    /**
     * @Route(
     *     "/revoke/invitation/{contractId}",
     *     name="revoke_invitation",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"GET"})
     */
    public function revokeInvitation($contractId)
    {
        $translator = $this->get('translator');
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        /** @var $contract Contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);

        if (!$contract) {
            return new JsonResponse(array('error' => $translator->trans('contract.not.found')));
        }

        $group = $this->getCurrentGroup();

        if (!$group) {
            return new JsonResponse(array('error' => $translator->trans('contract.not.found')));
        }

        if ($contract->getGroupId() !== $group->getId()) {
            return new JsonResponse(array('error' => $translator->trans('contract.not.found')));
        }
        /**
         * This contract don't have any payment this is just contract, so we can remove it from db
         */
        $tenant = $contract->getTenant();
        $landlord = $this->getUser();
        $this->get('project.mailer')->sendRjContractRemovedFromDbByLandlord(
            $tenant,
            $landlord,
            $contract
        );
        $em->remove($contract);
        $em->flush();

        return new JsonResponse(array());
    }

    /**
     * @Route(
     *     "/send/invitation/reminder/{contractId}",
     *     name="send_reminder_invitation",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"GET"})
     */
    public function sendReminderInvite($contractId)
    {
        $translator = $this->get('translator');
        $session = $this->get("session");
        $landlordReminder = $session->get('landlord_reminder');
        if (!$landlordReminder) {
            $landlordReminder = array();
        } else {
            $landlordReminder = json_decode($landlordReminder, true);
        }

        if (in_array($contractId, $landlordReminder)) {
            return new JsonResponse(array('error' => $translator->trans('contract.reminder.error.already.send')));
        }

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        /** @var $contract Contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);

        if (!$contract) {
            return new JsonResponse(array('error' => $translator->trans('contract.not.found')));
        }

        $group = $this->getCurrentGroup();

        if (!$group) {
            return new JsonResponse(array('error' => $translator->trans('contract.not.found')));
        }

        if ($contract->getGroupId() !== $group->getId()) {
            return new JsonResponse(array('error' => $translator->trans('contract.not.found')));
        }

        $tenant = $contract->getTenant();

        if ($tenant->getIsActive()) {
            $this->get('project.mailer')->sendRjTenantInviteReminderPayment($tenant, $this->getUser(), $contract);
        } else {
            $this->get('project.mailer')->sendRjTenantInviteReminder($tenant, $this->getUser(), $contract);
        }

        $landlordReminder[] = $contract->getId();

        $session->set('landlord_reminder', json_encode($landlordReminder));

        return new JsonResponse(array());
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
