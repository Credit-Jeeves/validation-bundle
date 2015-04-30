<?php

namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteTenantType;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\PublicBundle\Form\TenantType;
use CreditJeeves\DataBundle\Enum\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Process\Exception\LogicException;

class PublicController extends Controller
{
    const TYPE_PROPERTY = 'property';

    const TYPE_HOLDING = 'holding';

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
             * @var $propertyProcess PropertyProcess
             */
            $propertyProcess = $this->container->get('property.process');
            if (!$property->getGoogleReference() && $propertyProcess->isValidProperty($property)) {
                $propertyProcess->saveToGoogle($property);
            }

            return $this->redirect($this->generateUrl("iframe_new", array('id' => $propertyId)));
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
     * @Template()
     *
     * @return array
     */
    public function newAction($id, $type, Request $request)
    {
        if (false === in_array($type, [self::TYPE_HOLDING, self::TYPE_PROPERTY])) {
            return $this->createNotFoundException(sprintf('Undefined type "%s"'), $type);
        }
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
                        ->findByHoldingAndAlphaNumericSort($holding);
                }
            }
        }

        $google = $this->get('google');

        if (self::TYPE_PROPERTY === $type) {
            $property = $em->getRepository('RjDataBundle:Property')->findOneWithUnitAndAlphaNumericSort($id);
        } else {
            $holding = $em->getRepository("DataBundle:Holding")->find($id);
        }

        $form = $this->createForm($tenantType = new TenantType($em), $tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $password = $form->get('password')->getData();
            /** @var $tenant Tenant */
            $tenant = $form->getData();
            $password = $this->container->get('user.security.encoder.digest')
                ->encodePassword($password, $tenant->getSalt());
            $tenant->setPassword($password);
            $tenant->setCulture($this->container->getParameter('kernel.default_locale'));
            /** @var Unit $unit */
            $unit = $form->get('unit')->getData();
            $propertyIdForm = $form->get('propertyId')->getData();
            /** @var Property $propertyForm */
            $propertyForm = $em->getRepository('RjDataBundle:Property')
                ->findOneWithUnitAndAlphaNumericSort($propertyIdForm);

            $em->persist($tenant);
            $em->flush();
            /** @var ContractProcess $contractProcess */
            $contractProcess = $this->get('contract.process');
            $contractProcess->createContractFromTenantSide(
                $tenant,
                $propertyForm,
                $unit->getActualName(),
                $tenantType->getWaitingContract()
            );

            $this->get('project.mailer')->sendRjCheckEmail($tenant);

            $session->remove('holding_id');
            $session->remove('resident_id');

            return $this->redirectToRoute('user_new_send', ['userId' => $tenant->getId()]);
        }

        $propertyList = [];

        if (self::TYPE_PROPERTY === $type && $property) {
            $propertyList = $google->searchPropertyInRadius($property);

            if (isset($propertyList[$property->getId()])) {
                unset($propertyList[$property->getId()]);
            }

            $propertyList = array_merge([$property], $propertyList);

            $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($property->getId());

            if ($countGroup === 0) {
                return $this->redirectToRoute('iframe_invite', ['propertyId' => $id]);
            }
        }

        if (self::TYPE_HOLDING === $type && $holding) {
            $propertyList = $em->getRepository('RjDataBundle:Property')->findByHoldingAndAlphaNumericSort($holding);
        }

        if (true === isset($holdingPropertyList)) {
            $propertyList = $holdingPropertyList;
        }

        $parameters = [
            'form' => $form->createView(),
            'property' => (isset($property) && $property) ? $property : new Property(),
            'propertyList' => $propertyList,
            'countPropery' => count($propertyList),
            'id' => $id,
            'type' => $type,
        ];

        if (true === isset($contractProperties) && count($contractProperties) > 0) {
            $parameters['contractProperties'] = $contractProperties;
        }
        if (true === isset($contractUnits) && count($contractUnits) > 0) {
            $parameters['contractUnits'] = $contractUnits;
        }

        return $parameters;
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
}
