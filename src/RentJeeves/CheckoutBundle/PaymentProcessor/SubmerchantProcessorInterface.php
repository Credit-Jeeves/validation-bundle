<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface SubmerchantProcessorInterface extends PaymentProcessorInterface
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
     * @throws \Exception if PaymentAccount not registered
     *
     * @return true if PaymentAccount successfully registered
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
     * @param $paymentType is of type CreditJeeves\DataBundle\Enum\OrderPaymentType
     * @param $executeDate
     * @return Date the estimated deposit date
     */
    public function calculateDepositDate($paymentType, \DateTime $executeDate);
}
