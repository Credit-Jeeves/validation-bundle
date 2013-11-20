<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/ajax")
 * @author alex
 *
 */
class AjaxController extends Controller
{
    /**
     * @Route("/contracts", name="tenant_contracts")
     * @Template()
     */
    public function contractsAction()
    {
        return array();
    }

    /**
     * @Route(
     *  "/bureau/reporting",
     *  name="tenant_contract_reporting",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"},
     *  options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @return array
     */
    public function contractBureauReporting()
    {
        $request = $this->getRequest();
        $data = $request->request->all('data');
        $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($data['contract_id']);
        $action = $data['action'];
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if ($action == 'start') {
            $contract->setReporting(true);
        } else {
            $contract->setReporting(false);
        }
        $em->persist($contract);
        $em->flush();
        return new JsonResponse(array($action));
    }

    /**
     * @Route(
     *  "/contract/delete",
     *  name="tenant_contract_delete",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"},
     *  options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @return array
     */
    public function deleteContract()
    {
        $request = $this->getRequest();
        $data = $request->request->all('data');
        /** @var $contract Contract */
        $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($data['contract_id']);
        $em = $this->getDoctrine()->getManager();

        /**
         *  On this logic if else
         *  implement logic each describe on the ContractStatus class
         *  for more detail see it.
         */
        if (in_array($contract->getStatus(), array(ContractStatus::INVITE, ContractStatus::PENDING))) {
            $tenant = $contract->getTenant();
            $landlordUsersAdmin = $contract->getGroup()->getHolding()->getHoldingAdmin();

            if ($landlordUsersAdmin) {
                /**
                 *
                 * Notify holding admin each have relationship with this contract by email
                 *
                 * @var $landlord User
                 */
                foreach ($landlordUsersAdmin as $landlord) {
                    $this->get('project.mailer')->sendRjContractRemovedFromDbByTenant(
                        $tenant,
                        $landlord,
                        $contract
                    );
                }
            }

            $em->remove($contract);
        } elseif (in_array($contract->getStatus(), array(ContractStatus::APPROVED))) {
            $contract->setStatus(ContractStatus::DELETED);
            $em->persist($contract);
        } elseif (in_array($contract->getStatus(), array(ContractStatus::CURRENT))) {
            $contract->setStatus(ContractStatus::FINISHED);
            $em->persist($contract);
        }

        $em->flush();
        return new JsonResponse(array());
    }
}
