<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\ContractRepository;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\DateTime;
use Exception;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * 
 * @Route("/ajax")
 *
 */
class AjaxController extends Controller
{
    /* Property */


    private function getContract($contractId)
    {
        /**
         * @var $contract Contract
         */
        $contract = $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Contract')->find($contractId);
        $translator = $this->get('translator');
        /**
         * @var $contract Contract
         */
        if (empty($contract)) {
            throw new NotFoundHttpException(
                $translator->trans(
                    "outstanding.validate.contract.not.exist",
                    array(
                        '%%CONTRACTID%%' => $contractId
                    )
                )
            );
        }
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $group = $contract->getGroup();

        if (!$user->getGroups()->contains($group)) {
            throw new NotFoundHttpException(
                $translator->trans(
                    "outstanding.validate.contract.not.your",
                    array(
                        '%%CONTRACTID%%' => $contractId
                    )
                )
            );
        }

        return $contract;
    }

    /**
     * @Route(
     *     "/landlord/contract/monthToMonth/{contractId}",
     *     name="landlord_month_to_month",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function contractMonthToMonth($contractId, Request $request)
    {
        $contract = $this->getContract($contractId);
        $contract->setFinishAt(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($contract);
        $em->flush($contract);

        return new JsonResponse(
            array(
                'status'  => 'successful',
            )
        );
    }

    /**
     * @Route(
     *     "/landlord/contract/changeEndDate/{contractId}",
     *     name="landlord_change_end_date_contract",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function contractChangeEndDate($contractId, Request $request)
    {
        $finishAt = $request->request->get('finishAt', null);
        $finishAt = DateTime::createFromFormat('m/d/Y', $finishAt);
        $errors = DateTime::getLastErrors();

        if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            return new JsonResponse(
                array(
                    'status'  => 'error',
                    'errors'  => array(
                        'Invalid date',
                    )
                )
            );
        }
        $contract = $this->getContract($contractId);
        $contract->setFinishAt($finishAt);
        $em = $this->getDoctrine()->getManager();
        $em->persist($contract);
        $em->flush($contract);

        return new JsonResponse(
            array(
                'status'  => 'successful',
            )
        );
    }

    /**
     * @Route(
     *     "/landlord/contract/end/{contractId}",
     *     name="landlord_end_contract",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function contractEnd($contractId, Request $request)
    {
        $uncollectedBalance = $request->request->get('uncollectedBalance', 0);
        $uncollectedBalance = floatval($uncollectedBalance);

        $contract = $this->getContract($contractId);
        $contract->setStatus(ContractStatus::FINISHED);
        $contract->setUncollectedBalance($uncollectedBalance);
        $contract->setFinishAt(new DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($contract);
        $em->flush($contract);

        $landlord = $this->getUser();
        $tenant = $contract->getTenant();
        $this->get('project.mailer')->endContractByLandlord($contract, $landlord, $tenant);

        return new JsonResponse(
            array(
                'status'  => 'successful',
            )
        );
    }

    /**
     * @Route(
     *     "/property/all/list",
     *     name="landlord_properties_list_all",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     */
    public function getAllProperties()
    {
        $group = $this->getCurrentGroup();
        $repo = $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Property');
        $properties = $repo->getPropetiesAll($group);

        foreach ($properties as $property) {
            $item = $property->getItem($group);
            $items[] = $item;
        }

        return new JsonResponse($items);
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
    public function getPropertiesList(Request $request)
    {
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
    public function addProperty(Request $request)
    {
        $property = array();
        $itsNewProperty = false;
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
        /** @var Property $property */
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->findOneBy($propertySearch);
        if ($property && $request->request->has('isSingle')) {
            $property->setIsSingle($request->request->get('isSingle') == 'true');
        }
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
        } catch (Exception $e) {
            return new JsonResponse(
                array(
                    'message' => $this->get('translator')->trans(
                        'property.error.can_not_be_added',
                        array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
                    )
                ),
                500
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
    public function deleteProperty(Request $request)
    {
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
    public function getUnitsList(Request $request)
    {
        $result = array('property' => '', 'units' => array());
        $user = $this->getUser();
        $holding = $user->getHolding();
        $group = $this->getCurrentGroup();
        $data = $request->request->all('property_id');
        $property = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($data['property_id']);
        $result['property'] = $property->getAddress();
        $result['isSingle'] = $property->getIsSingle();
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
    private function checkContractBeforeRemove($unit)
    {

        if ($unit->getContracts()->count() > 0) {
            $contracts = $unit->getContracts();
            $em = $this->getDoctrine()->getManager();
            /**
             * @var Contract $contract
             */
            foreach ($contracts as $contract) {
                $contract->setStatus(ContractStatus::FINISHED);
                $em->persist($contract);
            }
            $em->flush();
        }

        if ($unit->getContracts()->count() <= 0) {
            //@TODO find best way for this implementation
            $this->get('soft.deleteable.control')->disable();
            return;
        }

        if ($unit->getContracts()->count() > 0) {
            //@TODO find best way for this implementation
            $this->get('soft.deleteable.control')->enable();
            return;
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
    public function saveUnitsList(Request $request)
    {
        $data = array();
        $names = array();
        $existingNames = array();
        $errorNames = array();
        $user = $this->getUser();
        $holding = $user->getHolding();
        $group = $this->getCurrentGroup();
        $data = $request->request->all('units');
        $property = $request->request->all('property');
        $parent = $this->getDoctrine()->getRepository('RjDataBundle:Property')->find($property['property_id']);
        if (empty($parent)) {
            return new JsonResponse($data);
        }
        $units = (isset($data['units']))? $data['units'] : array();
        $unitCome = array();
        foreach ($units as $key => $unit) {
            $id = (!empty($unit['id']))? $unit['id'] : uniqid();
            $unitCome[$id] = array(
                'id'    => $unit['id'],
                'name'  => $unit['name'],
                'isNew' => (empty($unit['id']))? true : false,
            );
        }
        $records = $this->getDoctrine()->getRepository('RjDataBundle:Unit')->getUnits($parent, $holding, $group);
        $em = $this->getDoctrine()->getManager();

        //Update Current Unit
        foreach ($records as $key => $entity) {
            foreach ($unitCome as $unitId => $unitData) {
                if ($entity->getId() == $unitId && !empty($unitData['name'])) {
                    $existingNames[] = $unitData['name'];
                    $entity->setName($unitData['name']);
                    $em->persist($entity);
                    unset($unitCome[$unitId]);
                    unset($records[$key]);
                }
            }
        }

        //Remove unit each does not exist anymore
        foreach ($records as $entity) {
            $this->checkContractBeforeRemove($entity);
            $em->remove($entity);
        }

        //Create new Unit
        foreach ($unitCome as $unit) {
            if ($unit['isNew'] & !empty($unit['name']) & !in_array($unit['name'], $existingNames)) {
                $entity = new Unit();
                $entity->setProperty($parent);
                $entity->setHolding($holding);
                $entity->setGroup($group);
                $entity->setName($unit['name']);
                $em->persist($entity);
                $existingNames[] = $unit['name'];
            } else {
                $errorNames[] = $unit['name'];
            }
        }

        $em->flush();
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
    public function getTenantsList(Request $request)
    {
        $items = array();
        $total = 0;
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
    public function getContractsList(Request $request)
    {
        //@TODO find best way for this implementation
        //For this functional need show unit which was removed
        $this->get('soft.deleteable.control')->disable();
        $items = array();
        $total = 0;
        $dataRequest = $request->request->all('data')['data'];
        $data = array('contracts' => array(), 'total' => 0, 'pagination' => array());
        $group = $this->getCurrentGroup();
        /** @var ContractRepository $repo */
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
            /** @var Contract $contract */
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
    public function getActionsList(Request $request)
    {
        //For this page need show unit each was removed
        //@TODO find best way for this implementation
        $this->get('soft.deleteable.control')->disable();
        $items = array();
        $page = $request->request->all();
        $data = $page['data'];

        $sortColumn = $data['sortColumn'];
        $isSortAsc = $data['isSortAsc'];
        $searchField = $data['searchCollum'];
        $searchText = $data['searchText'];

        $sortType = ($isSortAsc == 'true')? "ASC" : "DESC";

        $result = array('actions' => array(), 'total' => 0, 'pagination' => array());
        $group = $this->getCurrentGroup();
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Contract');
        $query = $repo->getActionsRequiredPageQuery(
            $group,
            $data['page'],
            $data['limit'],
            $sortColumn,
            $sortType,
            $searchField,
            $searchText
        );
        $contracts = $query->getQuery()->execute();
        $paidForArr = array();
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $contract->setStatusShowLateForce(true);
            $item = $contract->getItem();
            $item['paidForArr'] = $this->get('checkout.paid_for')->getArray($contract);
            $items[] = $item;
        }
        $total = $query->select('count(c)')
            ->setMaxResults(null)
            ->setFirstResult(null)
            ->getQuery()
            ->getSingleScalarResult();
        $result['actions'] = $items;
        $result['total'] = $total;
        $result['paidForArr'] = $paidForArr;
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
    public function saveContract(Request $request)
    {
        $errors = array();
        $response = array();
        $translator = $this->get('translator');
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $contract = $request->request->all('contract');
        $details = $contract['contract'];
        $action = 'edit';
        if (isset($details['action'])) {
            $action = $details['action'];
        }
        if (empty($details['amount']) || $details['amount'] <= 0) {
            $errors[] = $translator->trans('contract.error.rent');
        }
        if (empty($details['start'])) {
            $errors[] = $translator->trans('contract.error.start');
        }
        /**
         * @var $contract Contract
         */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($details['id']);
        $tenant = $contract->getTenant();
        $tenant->setFirstName($details['first_name']);
        $tenant->setLastName($details['last_name']);
        $tenant->setEmail($details['email']);
        $tenant->setPhone($details['phone']);
        $property = $em->getRepository('RjDataBundle:Property')->find($details['property_id']);

        if (!$property->isSingle() && empty($details['unit_id'])) {
            $errors[] = $translator->trans('contract.error.unit');
        }

        $unit = $em->getRepository('RjDataBundle:Unit')->find($details['unit_id']);
        $contract->setRent($details['amount']);
        $contract->setDueDate($details['dueDate']);
        $contract->setStartAt(new DateTime($details['start']));
        if (!empty($details['finish'])) {
            $contract->setFinishAt(new DateTime($details['finish']));
        } else {
            $contract->setFinishAt(null);
        }
        $contract->setTenant($tenant);
        $contract->setProperty($property);
        $contract->setUnit($unit);
        if (in_array($details['status'], array(ContractStatus::APPROVED)) & empty($errors)) {
            $contract->setStatusApproved();
            $this->get('project.mailer')->sendContractApprovedToTenant($contract);
        }

        if ($contract->getSettings()->getIsIntegrated()) {
            $contract->setIntegratedBalance($details['balance']);
        } else {
            $contract->setBalance($details['balance']);
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
            $contract->setStatus(ContractStatus::DELETED);
            $em->persist($contract);
            $em->flush();
            return new JsonResponse($response);
        }

        $validatorErrors = $this->get('validator')->validate($contract);
        /** @var ConstraintViolation $error */
        foreach ($validatorErrors as $error) {
            $errors[] = $translator->trans($error->getMessage());
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
            return new JsonResponse($response);
        }

        $em->persist($contract);
        $em->flush();

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
    public function resolveContract(Request $request)
    {
        $amount = null;
        $data = $request->request->all('data');
        if (!isset($data['action'])) {
            return new BadRequestHttpException('Empty input');
        }
        if (isset($data['amount'])) {
            $amount = $data['amount'];
        }
        try {
            $paidFor = new DateTime($data['paid_for']);
        } catch (Exception $e) {
            return new BadRequestHttpException('Invalid input', $e);
        }
        /** @var Contract $contract */
        $contract = $this->getDoctrine()
            ->getManager()
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
                if ($amount) {
                    // Create order
                    $order = new Order();
                    $order->setUser($tenant);
                    $order->setSum($amount);
                    $order->setStatus(OrderStatus::COMPLETE);
                    $order->setType(OrderType::CASH);
                    $em->persist($order);
                    // Create operation
                    $operation = new Operation();
                    $operation->setOrder($order);
                    $operation->setType(OperationType::RENT);
                    $operation->setContract($contract);
                    $operation->setAmount($amount);
                    $operation->setPaidFor($paidFor);
                    $em->persist($operation);
                    $contract->shiftPaidTo($amount);
                    $contract->setBalance($contract->getBalance() - $amount);
                    if ($contract->getSettings()->getIsIntegrated()) {
                        $contract->setIntegratedBalance($contract->getIntegratedBalance() - $amount);
                    }
                }
                // Change paid to date
                $contract->setStatus(ContractStatus::CURRENT);
                $em->persist($contract);
                $em->flush();
                break;
            case Contract::RESOLVE_UNPAID:
                // @TODO Here will be report to Experian
                break;
        }
        // TODO blank page detection
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
        //For this functional need show unit which was removed
        $this->get('soft.deleteable.control')->disable();
        $items = array();
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
        /** @var OrderRepository $repo */
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

    /**
     * @Route(
     *     "/deposit/list",
     *     name="landlord_deposits_list",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function getDepositsList(Request $request)
    {
        $this->get('soft.deleteable.control')->disable();

        $page = $request->request->get('page');
        $limit = $request->request->get('limit');
        $filter = $request->request->get('filter');

        $group = $this->getCurrentGroup();
        $repo = $this->get('doctrine.orm.default_entity_manager')->getRepository('DataBundle:Order');

        $total = $repo->getCountDeposits($group, $filter);
        $deposits = array();
        if ($total) {
            $deposits = $repo->getDepositedOrders($group, $filter, $page, $limit);
        }

        $result = array(
            'deposits' => $deposits,
            'total' => $total,
            'pagination' => $this->datagridPagination($total, $limit)
        );

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('payment');

        $content = $this->get('jms_serializer')->serialize($result, 'json', $context);

        return new Response($content, 200, array('Content-type' => 'application/json'));
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
        $reminderInvite = $this->get('reminder.invite');
        if ($reminderInvite->sendTenant($contractId, $this->getUser(), $this->getCurrentGroup())) {
            return new JsonResponse(array());
        }

        return new JsonResponse(array('error' => $reminderInvite->getError()));

    }

    /**
     * @Route(
     *     "/verify/",
     *     name="landlord_resend_verification",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function sendVerificationAction()
    {
        $landlord = $this->getUser();
        $this->get('project.mailer')->sendRjCheckEmail($landlord);

        return new JsonResponse();
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
