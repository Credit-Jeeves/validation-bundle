<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface PaymentProcessorInterface
{
    /**
     * Creates a new payment account for User and Group.
     * Returns payment account token.
     *
     * @param PaymentAccountData $data
     * @param Contract $contract
     * @return string
     */
    public function createPaymentAccount(PaymentAccountData $data, Contract $contract);

    /**
     * Executes order of a given payment type (rent or report).
     * Returns order status.
     *
     * @param Order $order
     * @param PaymentAccount $paymentAccount
     * @param string $paymentType
     * @return string
     */
    public function executeOrder(
        Order $order,
        PaymentAccount $paymentAccount,
        $paymentType = PaymentGroundType::RENT
    );

    /**
     * Loads report of a given type.
     * Returns DepositReport or ReversalReport.
     *
     * @param string $reportType
     * @param array $settings
     * @return PaymentProcessorReport
     */
    public function loadReport($reportType, array $settings = []);
}
