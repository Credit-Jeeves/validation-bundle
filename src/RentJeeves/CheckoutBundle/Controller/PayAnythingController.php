<?php

namespace RentJeeves\CheckoutBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use RentJeeves\CheckoutBundle\Form\Type\PayAnythingPaymentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/checkout/pay-anything")
 */
class PayAnythingController extends BaseController
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
            'isLocked' => $this->getEntityManager()->getRepository('RjDataBundle:Tenant')
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
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find($groupId);

        if (!$group) {
            throw new \InvalidArgumentException('Group is undefined.');
        }

        $depositAccounts = $group->getNotRentDepositAccountsForCurrentPaymentProcessor();

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
     * @Route("/payment", name="pay_anything_validate_payment", options={"expose"=true})
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \ErrorException
     */
    public function validatePaymentAction(Request $request)
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
        if (!$contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find($contractId)) {
            throw $this->createNotFoundException('Contract does not exist');
        }

        /**
         * @var PaymentAccount $paymentAccount
         */
        $accountId = $paymentType->get('paymentAccountId')->getData();
        $paymentAccount = $this->getEntityManager()->getRepository('RjDataBundle:PaymentAccount')->find($accountId);
        if (!$paymentAccount) {
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
        $contract = $this->getEntityManager()
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
                    $contract->getGroup()->getNotRentDepositAccountsForCurrentPaymentProcessor()
                ),
                'oneTimeUntilValue' => $this->container->getParameter('payment_one_time_until_value'),
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
            ->getDepositAccountForCurrentPaymentProcessor($depositAccountType);
        if (null !== $depositAccount) {
            $payment->setDepositAccount($depositAccount);
        } else {
            throw $this->createNotFoundException('DepositAccount cannot be null');
        }

        $this->getEntityManager()->persist($payment);
        $this->getEntityManager()->flush($payment);
    }
}
