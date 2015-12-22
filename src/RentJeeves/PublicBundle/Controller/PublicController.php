<?php

namespace RentJeeves\PublicBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteTenantType;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\PublicBundle\Form\TenantType;
use CreditJeeves\DataBundle\Enum\UserType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\Exception\LogicException;

class PublicController extends Controller
{
    const TYPE_PROPERTY = 'property';

    const TYPE_HOLDING = 'holding';

    const TYPE_GROUP = 'group';

    const SESSION_CREATE_INTEGRATION_USER = 'create_integration_user';

    /**
     * @Route("/iframe", name="iframe")
     * @Template()
     *
     * @return array
     */
    public function iframeAction()
    {
        return array();
    }

    /**
     * @Route("/tenant/invite/resend/{userId}", name="tenant_invite_resend", options={"expose"=true})
     * @Template("RjPublicBundle:Public:resendInvite.html.twig")
     *
     */
    public function resendInviteTenantAction($userId)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var $user Tenant
         */
        $user = $em->getRepository('RjDataBundle:Tenant')->find($userId);
        if (empty($user)) {
            throw new LogicException("User which such id {$userId} does not exist");
        }
        $contracts = $user->getContracts();
        $contract = null;
        //@TODO contract which created last
        foreach ($contracts as $contract) {
            if ($contract->getStatus() === ContractStatus::INVITE) {
                break;
            }
        }
        /**
         * @var $contract Contract
         */
        if (empty($contract)) {
            throw new LogicException("User which try to get resend invite - does not have contract with status INVITE");
        }
        //Save as is but, in general can be problem on this line
        //Because in group we have many landlord and don't know what exactly Landlord send invite
        //So we select random landlord for group, it's main problem in architecture
        $reminderInvite = $this->get('reminder.invite');
        $landlord = $em->getRepository('RjDataBundle:Landlord')->getLandlordByContract($contract);

        if (empty($landlord)) {
            throw new LogicException("Contract which such id {$contract->getId()} doesn't have Landlord");
        }

        if (!$reminderInvite->sendTenant($contract->getId(), $landlord)) {
            return array(
                'error' => $reminderInvite->getError()
            );
        }

        return array(
            'error' => false,
        );
    }

    /**
     * @Route("/public_iframe", name="public_iframe")
     * @Template()
     *
     * @return array
     */
    public function publicIframeAction()
    {
        return array();
    }

    /**
     * @Route("/checkProperty/{propertyId}", name="iframe_search_check", options={"expose"=true})
     * @Template()
     *
     * @return array
     */
    public function checkSearchAction($propertyId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /**
         * @var $property Property
         */
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);

        if (!$property) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($property->getId());

        if ($countGroup > 0) {
            /**
             * @var $propertyProcess PropertyManager
             */
            $propertyProcess = $this->container->get('property.manager');
            $propertyAddress = $property->getPropertyAddress();
            if (!$propertyAddress->getGoogleReference()) {
                $propertyProcess->saveToGoogle($property);
            }

            return $this->redirectToRoute('iframe_new_property', ['id' => $propertyId]);
        }

        return $this->redirect($this->generateUrl("iframe_invite", array('propertyId' => $propertyId)));
    }

    /**
     * @Route("/user/invite/{propertyId}", name="iframe_invite")
     * @Template()
     *
     * @return array
     */
    public function inviteAction($propertyId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);

        if (!$property) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $form = $this->createForm(
            new InviteTenantType()
        );

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $tenant = $form->getData()['tenant'];
            $invite = $form->getData()['invite'];
            $aForm = $request->request->get($form->getName());
            $password = $this->container->get('user.security.encoder.digest')
                ->encodePassword($aForm['tenant']['password']['Password'], $tenant->getSalt());
            $tenant->setPassword($password);
            $invite->setTenant($tenant);
            $invite->setProperty($property);
            $tenant->setCulture($this->container->parameters['kernel.default_locale']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($invite);
            $em->persist($tenant);
            $em->flush();

            $this->get('project.mailer')->sendRjCheckEmail($tenant);

            return $this->redirect($this->generateUrl('user_new_send', array('userId' => $tenant->getId())));
        }

        $view = $form->createView();

        return array(
            'address' => $property->getFullAddress(),
            'form' => $form->createView(),
            'propertyId' => $property->getId(),
        );
    }

    /**
     * @param string $accountingSystemType
     * @param Request $request
     * @return array
     */
    protected function checkAndSaveRequestData($accountingSystemType, Request $request)
    {
        $accountingSystemType = array_search($accountingSystemType, ApiIntegrationType::$importMapping);
        if ($accountingSystemType === false) {
            throw new \InvalidArgumentException('Accounting system type is invalid.');
        }
        $parameters = ['accsys' => $accountingSystemType];
        $requiredParams = ['resid', 'leasid', 'propid'];

        foreach ($requiredParams as $paramName) {
            if (!$paramValue = $request->get($paramName)) {
                throw new \InvalidArgumentException(
                    sprintf('Please provide required parameter "%s".', $paramName)
                );
            }
            $parameters[$paramName] = $paramValue;
        }

        $params = ['unitid', 'rent'];

        foreach ($params as $paramName) {
            if ($paramValue = $request->get($paramName)) {
                $parameters[$paramName] = $paramValue;
            }
        }

        $amounts = [];
        if ($appFee = $request->get('appfee')) {
            $amounts[DepositAccountType::APPLICATION_FEE] = $appFee;
        }
        if ($secDep = $request->get('secdep')) {
            $amounts[DepositAccountType::SECURITY_DEPOSIT] = $secDep;
        }
        $parameters['amounts'] = $amounts;

        /** @var Session $session */
        $session = $request->getSession();
        $session->set(self::SESSION_CREATE_INTEGRATION_USER, $parameters);

        return $parameters;
    }

    /**
     * @param string $accountingSystemType
     * @param string $externalPropertyId
     * @param string|null $externalUnitId
     * @return null|Property
     */
    protected function getPropertyByExternalParameters(
        $accountingSystemType,
        $externalPropertyId,
        $externalUnitId = null
    ) {
        $em = $this->getEntityManager();
        try {
            if ($externalUnitId) {
                return $em->getRepository('RjDataBundle:Property')
                    ->getPropertyByExternalPropertyUnitIds($accountingSystemType, $externalPropertyId, $externalUnitId);
            } else {
                return $em->getRepository('RjDataBundle:Property')
                    ->getPropertyByExternalPropertyId($accountingSystemType, $externalPropertyId);
            }
        } catch (NonUniqueResultException $e) {
            $this->get('logger')->emergency(
                sprintf(
                    'Find more then one property for parameters: accounting system "%s", external property "%s"%s',
                    $accountingSystemType,
                    $externalPropertyId,
                    $externalUnitId  ? ', external unit "' . $externalUnitId . '"' : ''
                )
            );
            throw new \LogicException('Should be found just 1 property by external parameters');
        }
    }

    /**
     * @param Property $property
     * @throws \LogicException
     */
    protected function checkPropertyBelongOneGroup(Property $property)
    {
        if ($property->getPropertyGroups()->count() > 1) {
            $this->get('logger')->emergency(
                sprintf(
                    'Property #%d should belong just to one group.',
                    $property->getId()
                )
            );
            throw new \LogicException('Property should belong just to one group.');
        }
        try {
            $this->getEntityManager()
                ->getRepository('RjDataBundle:Unit')
                ->createQueryBuilder('u')
                ->select('1')
                ->where('u.property = :property')
                ->having('COUNT(DISTINCT u.group) = 1')
                ->setParameter('property', 2)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $this->get('logger')->emergency(
                sprintf(
                    'Property #%d should have units that belong just to one group.',
                    $property->getId()
                )
            );
            throw new \LogicException('Property should have units that belong just to one group.');
        }
    }

    /**
     * @param string $accountingSystemType
     * @param Request $request
     * @return Response
     * @throws  BadRequestHttpException|NotFoundHttpException
     *
     * @Route(
     *     "/user/integration/new/{accountingSystemType}",
     *     requirements={
     *         "accountingSystemType" = "mri|resman|yardi|amsi"
     *     },
     *     name="new_integration_user"
     * )
     */
    public function newIntegrationUserAction($accountingSystemType, Request $request)
    {
        try {
            $requestData = $this->checkAndSaveRequestData($accountingSystemType, $request);

            if (isset($requestData['unitid'])) {
                $property = $this->getPropertyByExternalParameters(
                    $requestData['accsys'],
                    $requestData['propid'],
                    $requestData['unitid']
                );
            } else {
                $property = $this->getPropertyByExternalParameters(
                    $requestData['accsys'],
                    $requestData['propid']
                );
            }

            if (!$property) {
                throw $this->createNotFoundException('Property not found.');
            }

            $this->checkPropertyBelongOneGroup($property);

            return $this->redirectToRoute('iframe_new_property', ['id' => $property->getId()]);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (\LogicException $e) {
            throw new HttpException(412, 'We are scrambling our robots...');
        }
    }

    /**
     * @param int|null $id
     * @param string   $type
     * @param Request  $request
     *
     * @Route(
     *      "/user/new/{id}/{type}",
     *      name="iframe_new",
     *      defaults={
     *          "id"=null,
     *          "type"="property"
     *      },
     *      options={"expose"=true}
     * )
     */
    public function newAction($id, $type)
    {
        if (false === in_array($type, [self::TYPE_HOLDING, self::TYPE_PROPERTY, self::TYPE_GROUP])) {
            return $this->createNotFoundException(sprintf('Undefined type "%s"', $type));
        }

        if (self::TYPE_GROUP === $type) {
            return $this->forward('RjPublicBundle:Public:newWithGroup', ['id' => $id]);
        }

        if (self::TYPE_HOLDING === $type) {
            return $this->forward('RjPublicBundle:Public:newWithHolding', ['id' => $id]);
        }

        return $this->forward('RjPublicBundle:Public:newWithProperty', ['id' => $id]);
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @Route(
     *      "/user/new/{id}/property",
     *      name="iframe_new_property",
     *      defaults={
     *          "id"=null
     *      },
     *      options={"expose"=true}
     * )
     */
    public function newWithPropertyAction($id, Request $request)
    {
        /** @var Session $session */
        $session = $request->getSession();
        $em = $this->getEntityManager();

        $tenant = new Tenant();

        if (null !== $session->get('holding_id') || null !== $session->get('resident_id')) {
            $residentId = $session->get('resident_id', '');
            $holdingId = $session->get('holding_id', '');
            if (null === $holding = $em->getRepository('DataBundle:Holding')->find($holdingId)) {
                $session->remove('holding_id');
                $session->remove('resident_id');

                return new Response('Holding not found', Response::HTTP_BAD_REQUEST);
            }

            $resident = $em->getRepository('RjDataBundle:ResidentMapping')
                ->findOneResidentByHoldingAndResidentId($holding, $residentId);

            if (null !== $resident) {
                $session->remove('holding_id');
                $session->remove('resident_id');
                if (null != $inviteCode = $resident->getTenant()->getInviteCode()) { // not NULL or not ""

                    return $this->redirectToRoute('tenant_invite', ['code' => $inviteCode]);
                } else {
                    $session->getFlashBag()->add('error', 'new.user.error.without_invite_code');

                    return $this->redirectToRoute('fos_user_security_login');
                }
            } else {
                $contracts = $em->getRepository('RjDataBundle:ContractWaiting')
                    ->findAllByHoldingAndResidentId($holding, $residentId);
                if (!empty($contracts)) {
                    $contractIds = [];
                    foreach ($contracts as $contract) {
                        $contractIds[] = $contract->getId();
                    }
                    $contractUnits = $em->getRepository('RjDataBundle:Unit')->findAllByContractWaitingIds($contractIds);
                    $contractProperties = [];
                    foreach ($contractUnits as $unit) {
                        $contractProperties[] = $unit->getProperty();
                    }
                    $contractProperties = array_unique($contractProperties);

                    $tenant->setFirstName($contracts[0]->getFirstName());
                    $tenant->setLastName($contracts[0]->getLastName());
                } else {
                    $holdingPropertyList = $em->getRepository('RjDataBundle:Property')
                        ->findByHoldingOrderedByAddress($holding);
                }
            }
        }

        $form = $this->createForm($tenantType = new TenantType($em), $tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $tenant = $this->processNewTenantForm($form, $tenantType);
            } catch (\InvalidArgumentException $e) {
                throw new BadRequestHttpException($e->getMessage());
            } catch (\LogicException $e) {
                throw new HttpException(412, 'We are scrambling our robots...');
            }

            $session->remove('holding_id');
            $session->remove('resident_id');

            return $this->redirectToRoute('user_new_send', ['userId' => $tenant->getId()]);
        }

        $propertyList = [];
        $property = $em->getRepository('RjDataBundle:Property')->findOneWithUnitAndAlphaNumericSort($id);
        if (null !== $property) {
            $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($property->getId());

            if ($countGroup === 0) {
                return $this->redirectToRoute('iframe_invite', ['propertyId' => $id]);
            }

            $propertyList = [$property];
        }

        if (true === isset($holdingPropertyList)) {
            $propertyList = $holdingPropertyList;
        }

        if (false === isset($property) || false == $property) {
            $property = new Property();
            $propertyAddress = new PropertyAddress();
            $property->setPropertyAddress($propertyAddress);
        }

        $parameters = [
            'form' => $form->createView(),
            'property' => $property,
            'propertyList' => $propertyList,
            'countPropery' => count($propertyList),
            'id' => $id,
            'type' => self::TYPE_PROPERTY,
        ];

        if (true === isset($contractProperties) && count($contractProperties) > 0) {
            $parameters['contractProperties'] = $contractProperties;
        }
        if (true === isset($contractUnits) && count($contractUnits) > 0) {
            $parameters['contractUnits'] = $contractUnits;
        }
        if ($session->has(self::SESSION_CREATE_INTEGRATION_USER)) {
            $externalParams = $session->get(self::SESSION_CREATE_INTEGRATION_USER);
            if (isset($externalParams['unitid']) &&
                $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneBy([
                    'externalUnitId' => $externalParams['unitid']
                ])
            ){
                $unitId = $unitMapping->getUnit()->getId();
            } else {
                $unitId = Unit::SEARCH_UNIT_UNASSIGNED;
            }

            $parameters['unitId'] = $unitId;
        }

        return $this->render('RjPublicBundle:Public:new.html.twig', $parameters);
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @Route(
     *      "/user/new/{id}/holding",
     *      name="iframe_new_holding",
     *      options={"expose"=true}
     * )
     */
    public function newWithHoldingAction($id, Request $request)
    {
        $em = $this->getEntityManager();
        if (null === $holding = $em->getRepository('DataBundle:Holding')->find($id)) {
            $this->createNotFoundException('Holding not found');
        }

        $form = $this->createForm($tenantType = new TenantType($em), new Tenant());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $tenant = $this->processNewTenantForm($form, $tenantType);

            return $this->redirectToRoute('user_new_send', ['userId' => $tenant->getId()]);
        }

        $propertyList = $em->getRepository('RjDataBundle:Property')->findByHoldingOrderedByAddress($holding);

        $property = new Property();
        $propertyAddress = new PropertyAddress();
        $property->setPropertyAddress($propertyAddress);

        return $this->render('RjPublicBundle:Public:new.html.twig', [
            'form' => $form->createView(),
            'property' => $property,
            'propertyList' => $propertyList,
            'countPropery' => count($propertyList),
            'id' => $id,
            'type' => self::TYPE_HOLDING,
        ]);
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @Route(
     *      "/user/new/{id}/group",
     *      name="iframe_new_group",
     *      options={"expose"=true}
     * )
     */
    public function newWithGroupAction($id, Request $request)
    {
        $em = $this->getEntityManager();
        if (null === $group = $em->getRepository('DataBundle:Group')->find($id)) {
            $this->createNotFoundException('Group not found');
        }

        $form = $this->createForm($tenantType = new TenantType($em), new Tenant());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $tenant = $this->processNewTenantForm($form, $tenantType);

            return $this->redirectToRoute('user_new_send', ['userId' => $tenant->getId()]);
        }

        $propertyList = $em->getRepository('RjDataBundle:Property')->getAllPropertiesInGroupOrderedByAddress($group);

        $property = new Property();
        $propertyAddress = new PropertyAddress();
        $property->setPropertyAddress($propertyAddress);

        return $this->render('RjPublicBundle:Public:new.html.twig', [
            'form' => $form->createView(),
            'property' => $property,
            'propertyList' => $propertyList,
            'countPropery' => count($propertyList),
            'id' => $id,
            'type' => self::TYPE_GROUP,
        ]);
    }

    /**
     * @param FormInterface $form
     * @param TenantType $tenantType
     * @return Tenant
     */
    protected function processNewTenantForm(FormInterface $form, TenantType $tenantType)
    {
        $password = $form->get('password')->getData();
        /** @var Tenant $tenant */
        $tenant = $form->getData();
        $password = $this->container->get('user.security.encoder.digest')
            ->encodePassword($password, $tenant->getSalt());
        $tenant->setPassword($password);
        $tenant->setCulture($this->container->getParameter('kernel.default_locale'));
        $em = $this->getEntityManager();
        $em->persist($tenant);
        $session = $this->get('session');
        /** @var Unit $unit */
        $unit = $form->get('unit')->getData();
        /** @var Property $property */
        $property = $em->getRepository('RjDataBundle:Property')
            ->findOneWithUnitAndAlphaNumericSort($form->get('propertyId')->getData());
        $externalLeaseId = null;
        if ($session->has(self::SESSION_CREATE_INTEGRATION_USER)) {
            $externalParameters = $session->get(self::SESSION_CREATE_INTEGRATION_USER);
            if (!isset($externalParameters['leasid'])) {
                throw new \InvalidArgumentException('Lease id should be specified.');
            }
            $externalLeaseId = $externalParameters['leasid'];
            $rent = isset($externalParameters['rent']) ? $externalParameters['rent'] : null;
            $residentMapping = $this->createResidentMapping(
                $externalParameters,
                $property,
                $unit
            );
            $residentMapping->setTenant($tenant);
            $tenant->addResidentsMapping($residentMapping);
            $em->persist($residentMapping);
        }
        $em->flush();

        /** @var ContractProcess $contractProcess */
        $contractProcess = $this->get('contract.process');
        $contractProcess->createContractFromTenantSide(
            $tenant,
            $property,
            $unit->getActualName(),
            $tenantType->getWaitingContract(),
            $externalLeaseId,
            $rent
        );

        $this->get('project.mailer')->sendRjCheckEmail($tenant);

        return $tenant;
    }

    /**
     * @param  array $externalParams
     * @param  Property $property
     * @param  Unit $unit
     * @return ResidentMapping
     */
    protected function createResidentMapping(array $externalParams, Property $property, Unit $unit)
    {
        if (!isset($externalParams['resid'])) {
            throw new \InvalidArgumentException('Resident id should be specified.');
        }
        if (!isset($externalParams['propid'])) {
            throw new \InvalidArgumentException('External property id should be specified.');
        }
        if (!isset($externalParams['accsys'])) {
            throw new \InvalidArgumentException('Accounting system should be specified.');
        }
        $residentMapping = new ResidentMapping();
        $residentMapping->setResidentId($externalParams['resid']);
        $selectedUnit = null;
        if ($unit->getActualName() !== Unit::SEARCH_UNIT_UNASSIGNED) {
            $selectedUnit = $property->searchUnit($unit->getActualName());
        }
        $em = $this->getEntityManager();
        try {
            if ($selectedUnit) {
                $propertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')
                    ->getPropertyMappingByPropertyUnitAndExternalPropertyBelongAccountingSystem(
                        $property,
                        $selectedUnit,
                        $externalParams['propid'],
                        $externalParams['accsys']
                    );
            } else {
                $propertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')
                    ->getPropertyMappingByPropertyAndExternalPropertyBelongAccountingSystem(
                        $property,
                        $externalParams['propid'],
                        $externalParams['accsys']
                    );
            }
        } catch (NonUniqueResultException $e) {
            $this->get('logger')->emergency(
                sprintf(
                    'Find more then one property mapping for parameters:' .
                    ' property #%d,%s accounting system "%s", external property "%s"',
                    $property->getId(),
                    $selectedUnit ? ' and selected unit "' . $selectedUnit. '",' : '',
                    $externalParams['accsys'],
                    $externalParams['propid']
                )
            );
            throw new \LogicException('Should be find just one property mapping with this parameters.');
        }
        $residentMapping->setHolding($propertyMapping->getHolding());

        return $residentMapping;
    }

    /**
     * @Route("/user/check/{code}", name="tenant_new_check")
     * @Template()
     *
     * @return array
     */
    public function checkInviteAction($code)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' => $code));

        if (empty($user)) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $user->setInviteCode(null);
        $user->setIsActive(true);
        $em->persist($user);
        if ($user->getType() == UserType::LANDLORD) {
            $em->flush();

            return array(
                'signinUrl' => $this->get('router')->generate('fos_user_security_login')
            );
        }

        if ($user->getInvite()) {
            $invite = $user->getInvite();
            $this->get('invite.landlord')->invite($invite, $user);
            $em->remove($invite);
        }

        $em->flush();

        return array(
            'signinUrl' => $this->get('router')->generate('fos_user_security_login')
        );
    }

    /**
     * @Route("/unsub", name="unsubscribe_user")
     */
    public function unsubscribeUser(Request $request)
    {
        if (false == $email = $request->query->get('md_email')) {
            throw new \LogicException('Parameter \'md_email\' not found.');
        }
        $user = $this->getEntityManager()->getRepository('DataBundle:User')->findOneBy(['email' => $email]);
        if (null !== $user) {
            $user->setEmailNotification(false);
            $user->setOfferNotification(false);
            $this->getEntityManager()->flush($user);
        }

        return $this->render('RjPublicBundle:Public:unsubscribeUser.html.twig', ['email' => $email]);
    }
}
