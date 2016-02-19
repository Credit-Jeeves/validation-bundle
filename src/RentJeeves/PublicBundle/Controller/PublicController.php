<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\PublicBundle\Services\AccountingSystemIntegrationDataManager;
use RentJeeves\PublicBundle\Services\TenantProcessor;
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
     * @param string $accountingSystem
     * @param Request $request
     * @return Response
     * @throws  BadRequestHttpException|NotFoundHttpException|HttpException
     *
     * @Route(
     *     "/user/integration/new/{accountingSystem}",
     *     requirements={
     *         "accountingSystem" = "mri|resman|yardi|amsi"
     *     },
     *     name="new_integration_user"
     * )
     */
    public function newIntegrationUserAction($accountingSystem, Request $request)
    {
        try {
            $integrationDataManager = $this->get('accounting_system.integration.data_manager');
            $integrationDataManager->processRequestData($accountingSystem, $request);

            if ($integrationDataManager->hasMultiProperties()) {
                return $this->redirectToRoute('iframe_new');
            }

            $property = $integrationDataManager->getProperty();

            if (!$property) {
                throw $this->createNotFoundException('Property not found.');
            }

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

        $form = $this->createForm(new TenantType($em), $tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $tenant = $this->processNewTenantForm($form);
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

        $integrationDataManager = $this->get('accounting_system.integration.data_manager');
        if ($integrationDataManager->hasIntegrationData()) {
            $unitId = Unit::SEARCH_UNIT_UNASSIGNED;
            if ($integrationDataManager->hasMultiProperties()) {
                $parameters['propertyList'] = $integrationDataManager->getMultiProperties();
            } elseif ($integrationDataManager->getUnit()) {
                $unitId = $integrationDataManager->getUnit()->getId();
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

        $form = $this->createForm(new TenantType($em), new Tenant());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $tenant = $this->processNewTenantForm($form);

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

        $form = $this->createForm(new TenantType($em), new Tenant());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $tenant = $this->processNewTenantForm($form);

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
     * @return Tenant
     */
    protected function processNewTenantForm(FormInterface $form)
    {
        /** @var TenantProcessor $tenantProcessor */
        $tenantProcessor = $this->get('tenant.processor');
        /** @var AccountingSystemIntegrationDataManager $integrationDataManager */
        $integrationDataManager = $this->get('accounting_system.integration.data_manager');
        /** @var Property $property */
        $property = $form->get('propertyId')->getData();
        /** @var Unit $unit */
        $unit = $form->get('unit')->getData();
        $externalLeaseId = null;
        $rent = null;
        if (!$integrationDataManager->hasIntegrationData()) {
            $tenant = $tenantProcessor->createNewTenant($form->getData(), $form->get('password')->getData());
        } else {
            $residentMapping = $integrationDataManager->createResidentMapping($property, $unit->getActualName());
            $tenant = $tenantProcessor->createNewIntegratedTenant(
                $form->getData(),
                $form->get('password')->getData(),
                $residentMapping
            );
            $externalLeaseId = $integrationDataManager->getExternalLeaseId();
            $rent = $integrationDataManager->getRent();
        }
        /** @var ContractProcess $contractProcess */
        $contractProcess = $this->get('contract.process');
        $contractProcess->createContractFromTenantSide(
            $tenant,
            $property,
            $unit->getActualName(),
            $form->get('contractWaiting')->getData(),
            $externalLeaseId,
            $rent
        );

        $this->get('project.mailer')->sendRjCheckEmail($tenant);

        return $tenant;
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
