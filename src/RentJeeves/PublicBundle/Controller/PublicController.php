<?php

namespace RentJeeves\PublicBundle\Controller;

use FOS\UserBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Validators\TenantEmail;
use RentJeeves\DataBundle\Validators\TenantEmailValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteTenantType;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Alert;
use RentJeeves\PublicBundle\Form\TenantType;
use CreditJeeves\DataBundle\Enum\UserType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Validator\Validator;

class PublicController extends Controller
{
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
            return $this->redirect($this->generateUrl("iframe_new", array('propertyId'=>$propertyId)));
        }

        return $this->redirect($this->generateUrl("iframe_invite", array('propertyId'=>$propertyId)));
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
            return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$tenant->getId())));
        }

        $view = $form->createView();

        return array(
            'address'           => $property->getFullAddress(),
            'form'              => $form->createView(),
            'propertyId'        => $property->getId(),
        );
    }

    /**
     * @Route(
     *      "/user/new/{propertyId}/{holdingId}",
     *      name="iframe_new",
     *      defaults={
     *          "propertyId"=null,
     *          "holdingId"=0
     *      },
     *      options={"expose"=true}
     * )
     * @Template()
     *
     * @return array
     */
    public function newAction($propertyId, $holdingId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        $google = $this->get('google');

        $property = $em->getRepository('RjDataBundle:Property')
            ->findOneWithUnitAndAlphaNumericSort(
                $propertyId,
                $holdingId
            );
        $holding = $em->getRepository("DataBundle:Holding")->find($holdingId);
        $tenant = new Tenant();
        $form = $this->createForm(
            $tenantType = new TenantType($this->getDoctrine()->getManager()),
            $tenant
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $form->get('password')->getData();
            /**
             * @var $tenant Tenant
             */
            $tenant = $form->getData();
            $password = $this->container->get('user.security.encoder.digest')
                    ->encodePassword($password, $tenant->getSalt());
            $tenant->setPassword($password);
            $tenant->setCulture($this->container->parameters['kernel.default_locale']);
            /**
             * @var $unit Unit
             */
            $unit = $form->get('unit')->getData();
            $propertyIdForm = $form->get('propertyId')->getData();
            /**
             * @var $propertyForm Property
             */
            $propertyForm = $em->getRepository('RjDataBundle:Property')
                ->findOneWithUnitAndAlphaNumericSort($propertyIdForm);

            $em->persist($tenant);
            $em->flush();
            /**
             * @var $contractProcess ContractProcess
             */
            $contractProcess = $this->get('contract.process');
            $contractProcess->createContractFromTenantSide(
                $tenant,
                $propertyForm,
                $unit->getName(),
                $tenantType->getWaitingContract()
            );


            $this->get('project.mailer')->sendRjCheckEmail($tenant);
            return $this->redirect($this->generateUrl('user_new_send', array('userId' =>$tenant->getId())));
        }

        $propertyList = [];

        if ($property) {
            $propertyList = $google->searchPropertyInRadius(
                $property,
                $holding
            );

            if (isset($propertyList[$property->getId()])) {
                unset($propertyList[$property->getId()]);
            }

            $propertyList = array_merge(array($property), $propertyList);

            $countGroup = $em->getRepository('RjDataBundle:Property')->countGroup($property->getId());

            if ($countGroup <= 0) {
                return $this->redirect($this->generateUrl("iframe_invite", array('propertyId'=>$propertyId)));
            }
        }

        return array(
            'form'              => $form->createView(),
            'property'          => $property ? $property : new Property(),
            'propertyList'      => $propertyList,
            'countPropery'      => count($propertyList),
        );
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
            $this->get('invite.landord')->invite($invite, $user);
            $em->remove($invite);
        }

        $em->flush();

        return array(
            'signinUrl' => $this->get('router')->generate('fos_user_security_login')
        );
    }
}
