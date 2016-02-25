<?php
namespace RentJeeves\CheckoutBundle\Controller;

use RentJeeves\CheckoutBundle\Form\Type\PaymentBalanceOnlyType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorWeb;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorMobile;
use RentJeeves\CheckoutBundle\Services\UserDetailsTypeProcessor;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;

/**
 * @method \RentJeeves\DataBundle\Entity\Tenant getUser()
 * @Route("/checkout")
 */
class PayController extends Controller
{
    use FormErrors;
    use Traits\PaymentProcess;

    protected function createPaymentForm(Request $request, $mobile = false)
    {
        $contractId = $request->get('contract_id');
        /** @var Contract $contract */
        $contract = $this->getDoctrine()
            ->getManager()
            ->getRepository('RjDataBundle:Contract')
            ->findOneWithOperationsOrders($contractId);

        if (empty($contract)) {
            throw $this->createNotFoundException("Contract with '{$contractId}' not found");
        }

        $payBalanceOnly = $contract->getGroup()->getGroupSettings()->getPayBalanceOnly();
        if ($payBalanceOnly) {
            $formData = $request->get(PaymentBalanceOnlyType::NAME);
        } else {
            $formData = $request->get(PaymentType::NAME);
        }

        /** @var Payment $paymentEntity */
        $paymentEntity = null;
        if (!empty($formData['id'])) {
            $paymentEntity = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('RjDataBundle:Payment')
                    ->findOneWithContractOrdersOperations($formData['id']);
            $contract = $paymentEntity->getContract();
        }

        if (!empty($paymentEntity) &&
            $paymentEntity->getPaymentAccount()->getUser()->getId() !== $this->getUser()->getId()
        ) {
            throw $this->createNotFoundException("Payment with '{$formData['id']}' not found");
        }

        if ($mobile) {
            $attributes =  new AttributeGeneratorMobile();
        } else {
            $attributes =  new AttributeGeneratorWeb();
        }

        $formOptions = [
            'one_time_until_value' => $this->container->getParameter('payment_one_time_until_value'),
            'attributes' => $attributes,
            'open_day' => $contract->getGroupSettings()->getOpenDate(),
            'close_day' => $contract->getGroupSettings()->getCloseDate(),
        ];

        if ($payBalanceOnly) {
            $formType = new PaymentBalanceOnlyType();
            $formOptions['em'] = $this->getDoctrine()->getManager();
        } else {
            $dueDays = $contract->getSettings()->getDueDays();
            $formType = new PaymentType();
            $formOptions['paid_for'] = $this->container->get('checkout.paid_for')->getArray($contract);
            $formOptions['due_days'] = array_combine($dueDays, $dueDays);
        }

        $orderRepo = $this->getDoctrine()->getManager()->getRepository('DataBundle:Order');
        if ($contract->getGroup()->getOrderAlgorithm() === OrderAlgorithmType::PAYDIRECT &&
            $lastDTROrder = $orderRepo->getLastDTRPaymentByContract($contract)
        ) {
            $lastPaymentDate = clone $lastDTROrder->getCreatedAt();
            $lastPaymentDate->modify(
                '+' . (int) $this->container->getParameter('dod_dtr_payment_rolling_window') . ' days'
            );
            $formOptions['min_start_date'] = $lastPaymentDate;
        }

        return $this->createForm($formType, $paymentEntity, $formOptions);
    }

    /**
     * @Route("/payment", name="checkout_pay_payment", options={"expose"=true})
     * @Method({"POST"})
     */
    public function paymentAction(Request $request, $mobile = false)
    {
        $paymentType = $this->createPaymentForm($request, $mobile);
        $paymentType->handleRequest($request);
        if (!$paymentType->isValid()) {
            return $this->renderErrors($paymentType);
        }

        /** @var Payment $paymentEntity */
        $paymentEntity = $paymentType->getData();

        $contractId = $request->get('contract_id');
        /** @var Contract $contract */
        $contract = $this->getDoctrine()
            ->getManager()
            ->getRepository('RjDataBundle:Contract')
            ->find($contractId);

        if (!$paymentEntity->getId() && $activePayment = $contract->getActiveRentPayment()) {
            $this->get('logger')->alert('Trying to create duplicate payment for contract #' . $contractId);

            return new JsonResponse([
                'success' => true,
                'payment_id' => $activePayment->getId(),
            ]);
        }

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @Route("/user", name="checkout_pay_user", options={"expose"=true})
     * @Method({"POST"})
     */
    public function userAction(Request $request)
    {
        $userType = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());

        $userType->handleRequest($request);
        if (!$userType->isValid()) {
            return $this->renderErrors($userType);
        }
        /** @var $formProcessor UserDetailsTypeProcessor */
        $formProcessor = $this->get('user.details.type.processor');
        $formProcessor->save($userType, $this->getUser());

        return new JsonResponse([
            'success' => true,
            'newAddress' => $formProcessor->getIsNewAddress() ?
                $this->get('jms_serializer')->serialize(
                    $formProcessor->getAddress(),
                    'array'
                ) : null
        ]);
    }

    /**
     * @Route("/exec", name="checkout_pay_exec", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \ErrorException
     */
    public function execAction(Request $request)
    {
        $paymentType = $this->createPaymentForm($request);
        $paymentType->handleRequest($request);
        if (!$paymentType->isValid()) {
            return $this->renderErrors($paymentType);
        }

        $em = $this->getDoctrine()->getManager();

        /**
         * @var Contract $contract
         */
        $contractId = $paymentType->get('contractId')->getData();
        if (!$contract = $em->getRepository('RjDataBundle:Contract')->find($contractId)) {
            throw $this->createNotFoundException('Contract does not exist');
        }

        /**
         * @var PaymentAccount $paymentAccount
         */
        $accountId = $paymentType->get('paymentAccountId')->getData();
        if (!$paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')->find($accountId)) {
            throw $this->createNotFoundException('Payment account does not exist');
        }

        $recurring = false;
        $payBalanceOnly = $contract->getGroup()->getGroupSettings()->getPayBalanceOnly();
        if (!$payBalanceOnly && 'on' != $paymentType->get('ends')->getData()) {
            $recurring = true;
        }

        try {
            $this->savePayment(
                $request,
                $paymentType,
                $contract,
                $paymentAccount,
                $recurring,
                true            # verify user
            );
        } catch (\Exception $e) {
            $paymentType->addError(new FormError($e->getMessage()));

            return $this->renderErrors($paymentType);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/cancel/{id}", name="checkout_pay_cancel", options={"expose"=true})
     * @Method({"GET"})
     */
    public function cancelAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Payment $payment */
        $payment = $em->getRepository('RjDataBundle:Payment')->find($id);

        if (empty($payment) || $payment->getPaymentAccount()->getUser()->getId() != $this->getUser()->getId()) {
            throw $this->createNotFoundException("Payment with '{$id}' not found");
        }

        $payment->setClosed($this, PaymentCloseReason::USER_CANCELLED);
        $em->persist($payment);
        $em->flush($payment);

        return $this->redirect($request->headers->get('referer'));
    }
}
