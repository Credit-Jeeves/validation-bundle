<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CheckoutBundle\Tests\Unit\Payment\OrderManagement\OrderStatusManager\OrderSubmerchantStatusManagerCase;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\CheckoutBundle\PaymentProcessor\SubmerchantProcessorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RuntimeException;
use DateTime;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;

/**
 * @Route("/ajax")
 * @author alex
 *
 */
class AjaxController extends Controller
{
    const TENANT_PAYMENTS_LIMIT = 10;
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
     *      "/tenant_payments/{page}/{contractId}/{limit}",
     *      name="tenant_payments",
     *      defaults={"_format":"json"},
     *      requirements={"_format"="json"},
     *      options={"expose"=true}
     * )
     * @Method({"GET"})
     */
    public function paymentsAction($page = 1, $contractId = null, $limit = self::TENANT_PAYMENTS_LIMIT)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository('DataBundle:Order');

        $this->get('soft.deleteable.control')->disable();
        $orders = $repo->getTenantPayments($this->getUser(), $page, $contractId, $limit);

        $totalOrdersAmount = $repo->getTenantPaymentsAmount($this->getUser(), $contractId);
        $pages = ceil($totalOrdersAmount / $limit);

        // can't use jms_serializer since order already has handlerCallback used in another serialization
        array_walk(
            $orders,
            function (&$order) {
                $order = $order->getTenantPayment();
            }
        );
        $this->get('soft.deleteable.control')->enable();

        return new JsonResponse(array('tenantPayments' => $orders, 'pages' => array($pages)));
    }

    /**
     * @Route(
     *      "/deliveryDate/{contractId}/{executionDate}/{paymentType}",
     *      name="delivery_date",
     *      defaults={"_format":"json"},
     *      requirements={"_format"="json"},
     *      options={"expose"=true}
     * )
     * @Method({"GET"})
     */
    public function getDeliveryDate($contractId, $executionDate, $paymentType){
        $repo = $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Contract');
        /** @var Contract $contract */
        $contract = $repo->find($contractId);
        /** @var SubmerchantProcessorInterface $paymentProcessor */
        $paymentProcessor= $this->get('payment_processor.factory')->getPaymentProcessor($contract->getGroup());

//OrderPaymentType $paymentType, \DateTime $executeDate
        $depositDate = $paymentProcessor->calculateDepositDate($paymentType, new DateTime($executionDate));

        return new JsonResponse($depositDate);
    }

    /**
     * @Route(
     *     "/verify/",
     *     name="tenant_resend_verification",
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     */
    public function sendVerificationAction()
    {
        $tenant = $this->getUser();
        $this->get('project.mailer')->sendRjCheckEmail($tenant);

        return new JsonResponse();
    }
}
