<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\LandlordBundle\Form\ContractType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\LandlordBundle\Form\InviteTenantContractType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use \DateTime;

class TenantsController extends Controller
{
    /**
     * @Route("/tenants", name="landlord_tenants")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        $form = $this->createForm(
            new InviteTenantContractType($this->getUser(), $this->getCurrentGroup())
        );

        $data = array(
            'nGroups'   => $groups->count(),
            'Group'     => $this->getCurrentGroup(),
            'form'      => $form->createView(),
        );

        return $data;
    }

    /**
     * @Route(
     *     "/tenant/invite/save",
     *     name="landlord_invite_save",
     *     options={"expose"=true}
     * )
     * @Template()
     */
    public function saveInviteTenantAction()
    {
        /** @var $user Landlord */
        $user = $this->getUser();
        /** @var $group Group */
        $group = $this->get("core.session.landlord")->getGroup();
        $canInvite = false;
        /**
         * Only landlord with setup merchant name can invite tenant
         */
        if (!empty($group)) {
            $merchantName = $group->getMerchantName();
            $canInvite = (!empty($merchantName))? true : false;
        }
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(
            new InviteTenantContractType($this->getUser(), $group)
        );
         
        $request = $this->get('request');
        if ($request->getMethod() == 'POST' && $canInvite) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $tenant = $form->getData()['tenant'];
                $contract = $form->getData()['contract'];
                $finishAtType = $form->get('contract')->get('finishAtType')->getData();

                if ($finishAtType === ContractType::MONTH_TO_MONTH) {
                    $contract->setFinishAt(null);
                }
                $tenant->setCulture($this->container->parameters['kernel.default_locale']);
                $tenantInDb = $this->getDoctrine()->getRepository('RjDataBundle:Tenant')->findOneBy(
                    array(
                        'email' => $tenant->getEmail(),
                    )
                );
                if ($tenantInDb) {
                    unset($tenant);
                    $tenant = $tenantInDb;
                } else {
                    $tenant->setPassword(md5(md5(1)));
                }
                $contract->setStatus(ContractStatus::INVITE);
                $contract->setTenant($tenant);
                $tenant->addContract($contract);
                $user = $this->getUser();
                $holding = $user->getHolding();
                $group = $this->getCurrentGroup();
                $contract->setHolding($holding);
                $contract->setGroup($group);
                $date = DateTime::createFromFormat('Y-m-d', $contract->getStartAt());
                $contract->setStartAt($date);
                if ($contract->getFinishAt() !== null) {
                    $date = DateTime::createFromFormat('Y-m-d', $contract->getFinishAt());
                    $contract->setFinishAt($date);
                }
                $em->persist($contract);
                $em->persist($tenant);
                $em->flush();

                $this->get('project.mailer')->sendRjTenantInvite($tenant, $user, $contract);
            }
        }

        return $this->redirect($this->generateUrl('landlord_tenants'));
    }
}
