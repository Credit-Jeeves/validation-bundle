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
use Symfony\Component\HttpFoundation\Request;
use RuntimeException;
use DateTime;

/**
 * @Route("/ajax")
 * @author alex
 *
 */
class AjaxController extends Controller
{
    const TENANT_PAYMENTS_LIMIT = 5;
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
    public function contractBureauReporting(Request $request)
    {
        $contractId = $request->request->get('contractId');
        $reportToExperian = $request->request->get('experianReporting') === 'true';
        $reportToTransUnion = $request->request->get('transUnionReporting') === 'true';

        /** @var Contract $contract */
        $contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($contractId);
        if (!$contract) {
            throw new RuntimeException('Contract not found');
        }

        $contract->setReportToExperian($reportToExperian);
        $contract->setReportToTransUnion($reportToTransUnion);

        // if reporting is being turning on for the first time - set start date
        $now = new DateTime();
        if (!$contract->getExperianStartAt() && $reportToExperian) {
            $contract->setExperianStartAt($now);
        }

        if (!$contract->getTransUnionStartAt() && $reportToTransUnion) {
            $contract->setTransUnionStartAt($now);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($contract);
        $em->flush();

        return new JsonResponse();
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

    /**
     * @Route(
     *      "/tenant_payments/{page}/{contractId}",
     *      name="tenant_payments",
     *      defaults={"_format"="json"},
     *      requirements={"_format"="json"},
     *      options={"expose"=true}
     * )
     * @Method({"GET"})
     */
    public function paymentsAction($page = 1, $contractId = null)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository('DataBundle:Order');

        $orders = $repo->getTenantPayments($this->getUser(), $page, $contractId, self::TENANT_PAYMENTS_LIMIT);

        $totalOrdersAmount = $repo->getTenantPaymentsAmount($this->getUser(), $contractId);
        $pages = ceil($totalOrdersAmount / self::TENANT_PAYMENTS_LIMIT);

        // can't use jms_serializer since order already has handlerCallback used in another serialization
        array_walk(
            $orders,
            function (&$order) {
                $order = $order->getTenantPayment();
            }
        );

        return new JsonResponse(array('tenantPayments' => $orders, 'pages' => array($pages)));
    }
}
