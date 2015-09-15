<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CheckoutBundle\Form\Type\PayAnythingPaymentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/checkout/pay-anything")
 */
class PayAnythingController extends Controller
{
    use FormErrors;

    /**
     * @Template()
     */
    public function payAction()
    {
        $payAnythingPaymentType = $this->createForm(
            new PayAnythingPaymentType(),
            null,
            ['oneTimeUntilValue' => $this->container->getParameter('payment_one_time_until_value')]
        );

        return [
            'payAnythingPaymentType' => $payAnythingPaymentType->createView(),
            'isLocked' => $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Tenant')
                ->isPaymentProcessorLocked($this->getUser())
        ];
    }

    /**
     * @param $groupId
     * @Route(
     *     "/pay-for/list/{groupId}",
     *     name="pay_anything_pay_for_list",
     *     options={"expose"=true}
     * )
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function payForLisAction($groupId)
    {
        /** @var Group $group */
        $group = $this->getDoctrine()->getManager()->getRepository('DataBundle:Group')->find($groupId);

        if (!$group) {
            throw new \InvalidArgumentException('Group is undefined.');
        }

        $depositAccounts = $group->getNotRentDepositAccounts();

        $depositAccountsList = [];

        DepositAccountType::setTranslator([$this->get('translator'), 'trans']);

        foreach ($depositAccounts as $depositAccount) {
            $depositAccountsList[]  = [
                'name' => DepositAccountType::title($depositAccount->getType()),
                'value' => $depositAccount->getType(),
            ];
        }

        return new JsonResponse($depositAccountsList);
    }

    /**
     * @Route("/payment", name="pay_anything_payment", options={"expose"=true})
     * @Method({"POST"})
     */
    public function paymentAction(Request $request)
    {
        $paymentType = $this->createPaymentForm($request);
        $paymentType->handleRequest($request);
        if (!$paymentType->isValid()) {
            return $this->renderErrors($paymentType);
        }

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @Route("/exec", name="pay_anything_exec", options={"expose"=true})
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

        /**
         * @var Contract $contract
         */
        $contractId = $paymentType->get('contractId')->getData();
        if (!$contract = $this->getDoctrine()->getRepository('RjDataBundle:Contract')->find($contractId)) {
            throw $this->createNotFoundException('Contract does not exist');
        }

        /**
         * @var PaymentAccount $paymentAccount
         */
        $accountId = $paymentType->get('paymentAccountId')->getData();
        if (!$paymentAccount = $this->getDoctrine()->getRepository('RjDataBundle:PaymentAccount')->find($accountId)) {
            throw $this->createNotFoundException('Payment account does not exist');
        }

        $this->savePayment(
            $paymentType->getData(),
            $contract,
            $paymentAccount,
            $paymentType->get('payFor')->getData()
        );

        return new JsonResponse([
            'success' => true
        ]);

    }

    /**
     * @param Request $request
     * @return \Symfony\Component\Form\Form
     */
    protected function createPaymentForm(Request $request)
    {
        $contractId = $request->get('contract_id');
        /** @var Contract $contract */
        $contract = $this->getDoctrine()
            ->getManager()
            ->getRepository('RjDataBundle:Contract')
            ->findOneBy(['id' => $contractId, 'tenant' => $this->getUser()]);

        if (!$contract) {
            throw $this->createNotFoundException(sprintf('Contract with id "%d" not found', $contractId));
        }

        return $this->createForm(
            new PayAnythingPaymentType(),
            null,
            [
                'availablePayFor' => DepositAccountType::getAvailableChoices(
                    $contract->getGroup()->getNotRentDepositAccounts()
                ),
                'oneTimeUntilValue' => $this->container->getParameter('payment_one_time_until_value'),
                'openDay' => $contract->getGroup()->getGroupSettings()->getOpenDate(),
                'closeDay' => $contract->getGroup()->getGroupSettings()->getCloseDate(),
            ]
        );
    }

    /**
     * @param Payment $payment
     * @param Contract $contract
     * @param PaymentAccount $paymentAccount
     * @param string $depositAccountType should be valid DepositAccountType
     */
    protected function savePayment(
        Payment $payment,
        Contract $contract,
        PaymentAccount $paymentAccount,
        $depositAccountType
    ) {
        $payment->setContract($contract);
        $payment->setPaymentAccount($paymentAccount);

        $depositAccount = $contract
            ->getGroup()
            ->getDepositAccount($depositAccountType, $paymentAccount->getPaymentProcessor());
        if (null !== $depositAccount) {
            $payment->setDepositAccount($depositAccount);
        } else {
            throw $this->createNotFoundException('DepositAccount cannot be null');
        }

        $this->getDoctrine()->getManager()->persist($payment);
        $this->getDoctrine()->getManager()->flush();
    }
}
