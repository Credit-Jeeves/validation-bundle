<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface SubmerchantProcessorInterface
{
    /**
     * Register a paymentAccount with the target deposit account for the PaymentProcessor so we can make a payment.
     *
     * Call this method whenever scheduling a new payment and right before executing a payment to ensure the payment
     * processor has the proper configuration to facilitate the transfer.
     *
     * This method should be idempotent -- so if the PaymentAccount is already created and registered,
     * then no change should take place.
     *
     * @param AccountData $accountData Mapped PaymentAccount
     * @param DepositAccount $depositAccount DepositAccount to register PaymentAccount to
     *
     * @return bool if true, then success, else a failure occurred.
     */
    public function registerPaymentAccount(
        AccountData $accountData,
        DepositAccount $depositAccount
    );

    /**
     * @param AccountData $accountData
     * @return bool if true, then success, else a failure occurred.
     */
    public function modifyPaymentAccount(
        AccountData $accountData
    );

    /**
     * @param PaymentAccount $paymentAccount
     * @return bool if true, then success, else a failure occurred.
     */
    public function unregisterPaymentAccount(
        PaymentAccount $paymentAccount
    );

    /**
     * Register billingAccount with the target deposit account for the PaymentProcessor so we can make a payment.
     *
     * Use this the same way as we use the registerPaymentAccount() method -- but for BillingAccount entities
     *
     * @param AccountData $accountData Mapped BillingAccount
     * @param Landlord $landlord
     *
     * @return bool if true, then success, else a failure occurred.
     */
    public function registerBillingAccount(
        AccountData $accountData,
        Landlord $landlord
    );

    /**
     * Executes order of a given payment type (rent, report or charge).
     * Returns true or false.
     *
     * @param  Order                                               $order
     * @param  PaymentAccountInterface                             $accountEntity BillingAccount or PaymentAccount
     * @param  string                                              $paymentType one of PaymentGroundType
     * @return bool
     * @throws PaymentProcessorInvalidArgumentException|\Exception
     */
    public function executeOrder(
        Order $order,
        PaymentAccountInterface $accountEntity,
        $paymentType = PaymentGroundType::RENT
    );

    /**
     * Loads payment processor report.
     *
     * @return PaymentProcessorReport
     */
    public function loadReport();

    /**
     * @param $paymentType is of type CreditJeeves\DataBundle\Enum\OrderPaymentType
     * @param $executeDate
     * @return Date the estimated deposit date
     */
    public function calculateDepositDate($paymentType, \DateTime $executeDate);

    /**
     * Returns the name of payment processor.
     *
     * @return string
     */
    public function getName();

    /**
     * Generates reversed batch id for given order.
     *
     * @param Order $order
     * @return string
     */
    public function generateReversedBatchId(Order $order);
}
