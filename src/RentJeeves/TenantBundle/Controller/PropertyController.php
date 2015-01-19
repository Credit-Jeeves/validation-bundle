<?php

namespace RentJeeves\TenantBundle\Controller;

use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\TenantBundle\Services\InviteLandlord;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\PublicBundle\Form\InviteType;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;

/**
 * @Route("/property")
 * @author Alexandr Sharamko
 *
 */
class PropertyController extends Controller
{
    /**
     * @Route("/add", name="property_add")
     * @Route("/add/{propertyId}", name="property_add_id", options={"expose"=true})
     * @Template()
     */
    public function addAction($propertyId = null)
    {
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            return $this->addProperty();
        }

        $em = $this->getDoctrine()->getManager();
        $google = $this->get('google');
        $property = null;
        $propertyList = array();
        $propertyListHaveLandlord = array();
        $propertyListInvite = array();

        if (is_null($propertyId)) {
            return array(
                'property'                      => $property,
                'propertyListHaveLandlord'      => $propertyListHaveLandlord,
                'countProperyHaveLandlord'      => count($propertyListHaveLandlord),
                'propertyListInvite'            => $propertyListInvite,
                'countProperyInvite'            => count($propertyListInvite),
                'propertyId'                    => $propertyId
            );
        }

        $property = $em->getRepository('RjDataBundle:Property')->findOneWithUnitAndAlphaNumericSort($propertyId);
        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }
        
        $propertyList = $google->searchPropertyInRadius($property);
        
        if (isset($propertyList[$property->getId()])) {
            unset($propertyList[$property->getId()]);
        }

        $propertyList = array_merge(array($property), $propertyList);

        foreach ($propertyList as $key => $propertyValue) {
            if ($propertyValue->hasLandlord()) {
                $propertyListHaveLandlord[$key] = $propertyValue;
            } else {
                $propertyListInvite[$key] = $propertyValue;
            }
        }

        return array(
            'property'                      => $property,
            'propertyListHaveLandlord'      => $propertyListHaveLandlord,
            'countProperyHaveLandlord'      => count($propertyListHaveLandlord),
            'propertyListInvite'            => $propertyListInvite,
            'countProperyInvite'            => count($propertyListInvite),
            'propertyId'                    => $propertyId
        );
    }

    protected function addProperty()
    {
        $request = $this->get('request');
        $propertyId = $request->request->get('propertyId');
        $em = $this->getDoctrine()->getManager();
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        if (!$property) {
            throw $this->createNotFoundException('The property does not exist');
        }
        $unitName = $request->request->get('unit'.$property->getId());
        $unitNew = $request->request->get('unitNew'.$property->getId());
        $unitSearch = Unit::SEARCH_PROPERTY_NEW_NAME;
        if (!empty($unitName) && $unitName != 'new') {
            $unitSearch = $unitName;
        } elseif (!empty($unitNew)) {
            $unitSearch = $unitNew;
        }
        $tenant = $this->getUser();

        /**
         * @var $contractProcess ContractProcess
         */
        $contractProcess = $this->get('contract.process');
        $contractProcess->createContractFromTenantSide($tenant, $property, $unitSearch);

        return $this->redirect($this->generateUrl('tenant_homepage'), 301);
    }

    /**
     * @Route("/invite/{propertyId}", name="tenant_invite_landlord", options={"expose"=true})
     * @Template()
     */
    public function inviteLandlordAction($propertyId)
    {
        $em = $this->getDoctrine()->getManager();
        $property = $em->getRepository('RjDataBundle:Property')->find($propertyId);
        if (!$property) {
            throw $this->createNotFoundException('The property does not exist.');
        }

        $form = $this->createForm(
            new InviteType()
        );

        $request = $this->get('request');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $invite = $form->getData();
            $invite->setProperty($property);
            $invite->setTenant($this->getUser());
            /** @var InviteLandlord $inviteProcessor */
            $inviteProcessor = $this->get('invite.landlord');
            $inviteProcessor->invite($invite, $this->getUser());

            if (!count($inviteProcessor->getErrors())) {
                return $this->redirect($this->generateUrl('tenant_homepage'), 301);
            }

            foreach ($inviteProcessor->getErrors() as $error) {
                $form->addError($error);
            }
        }

        return array(
            'property'          => $property,
            'form'              => $form->createView(),
            'address'           => $property->getFullAddress(),
        );
    }
}
