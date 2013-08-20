<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\CoreBundle\Controller\LandlordController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\LandlordBundle\Form\InviteTenantContractType;
use RentJeeves\DataBundle\Enum\ContractStatus;

class TenantController extends Controller
{
    /**
     *@Route(
     *     "/tenant/new",
     *     name="landlord_tenant_new",
     *     options={"expose"=true}
     * )
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();
        return array(
            'nGroups' => $groups->count(),
            'Group' => $this->getCurrentGroup(),
        );
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
        $form = $this->createForm(
            new InviteTenantContractType($this->getUser())
        );

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $tenant = $form->getData()['tenant'];
                $tenant->setCulture($this->container->parameters['kernel.default_locale']);
                $contract = $form->getData()['contract'];
                $tenant->setPassword(md5(md5(1)));
                $contract->setStatus(ContractStatus::INVITE);
                $contract->setTenant($tenant);
                $user = $this->getUser();
                $holding = $user->getHolding();
                $group = $this->getCurrentGroup();
                $contract->setHolding($holding);
                $contract->setGroup($group);
                $date = \DateTime::createFromFormat('Y-m-d', $contract->getStartAt());
                $contract->setStartAt($date);
                $date = \DateTime::createFromFormat('Y-m-d', $contract->getFinishAt());
                $contract->setFinishAt($date);

                $em = $this->getDoctrine()->getManager();
                $em->persist($contract);
                $em->persist($tenant);
                $em->flush();

                $this->get('creditjeeves.mailer')->sendRjTenantInvite($tenant, $user, $contract);
            }
        }

        return $this->redirect($this->generateUrl('landlord_tenants'));
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        } else {
            foreach ($form->getErrors() as $key => $error) {
                $errors[] = $error->getMessage();
            }
        }

        return $errors;
    }
}
