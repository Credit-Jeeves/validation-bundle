<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\ComponentBundle\Service\ResidentManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Form\ContractType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use RentJeeves\LandlordBundle\Form\InviteTenantContractType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use \DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolation;

class TenantsController extends Controller
{
    /**
     * @Route("/tenants", name="landlord_tenants")
     */
    public function indexAction()
    {
        $form = $this->createForm(
            new InviteTenantContractType($this->getUser(), $this->getCurrentGroup())
        );

        return $this->render(
            'LandlordBundle:Tenants:index.html.twig',
            [
                'nGroups' => $this->getGroups()->count(),
                'Group' => $this->getCurrentGroup(),
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route(
     *     "/tenant/invite/save",
     *     name="landlord_invite_save",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="html|json"},
     *     options={"expose"=true}
     * )
     * @Template()
     * @Method({"POST"})
     */
    public function saveInviteTenantAction()
    {
        /** @var Landlord $user */
        $user = $this->getUser();
        /** @var Group $group */
        $group = $this->get("core.session.landlord")->getGroup();
        $canInvite = false;
        /**
         * Only landlord with setup merchant name can invite tenant
         */
        if (!empty($group)) {
            $depositAccount = $group->getRentDepositAccountForCurrentPaymentProcessor();
            $merchantName = $depositAccount ? $depositAccount->getMerchantName() : '';
            $canInvite = (!empty($merchantName)) ? true : false;
        }

        $em = $this->getDoctrine()->getManager();
        $errors = [];

        $form = $this->createForm(
            new InviteTenantContractType($this->getUser(), $group)
        );

        $request = $this->get('request');
        $translator = $this->get('translator');
        if ($request->getMethod() == 'POST' && $canInvite) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var Tenant $tenant */
                $tenant = $form->getData()['tenant'];
                /** @var Contract $contract */
                $contract = $form->getData()['contract'];
                $residentMapping = isset($form->getData()['resident']) ? $form->getData()['resident'] : null;
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

                if ($residentMapping) {
                    $residentMapping->setHolding($holding);
                    $residentMapping->setTenant($tenant);
                    /**
                     * @var $resident ResidentManager
                     */
                    $resident = $this->get('resident_manager');
                    $errors = array_merge(
                        $errors,
                        $resident->validate($this->getUser(), $residentMapping)
                    );
                }

                $validatorErrors = $this->get('validator')->validate($contract);
                /** @var ConstraintViolation $error */
                foreach ($validatorErrors as $error) {
                    $errors[] = $translator->trans($error->getMessage());
                }
            }

            foreach ($form->getErrors() as $error) {
                $errors[] = $translator->trans($error->getMessage());
            }
        }

        $response = [];
        if (!empty($errors)) {
            $response['errors'] = $errors;

            return new JsonResponse($response);
        }

        if (!empty($tenant) && !empty($contract)) {
            $this->get('project.mailer')->sendRjTenantInvite($tenant, $user, $contract);
            $em->flush();
        }

        return new JsonResponse($response);
    }
}
