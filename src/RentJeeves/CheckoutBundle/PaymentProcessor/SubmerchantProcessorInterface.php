<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface SubmerchantProcessorInterface
{
    /**
     * Creates a new payment account or registers a new deposit account for already existing payment account.
     *
     * @param  AccountData $accountData Mapped PaymentAccount.
     * @param  DepositAccount $depositAccount DepositAccount to register PaymentAccount to.
     */
    public function registerPaymentAccount(
        AccountData $accountData,
        DepositAccount $depositAccount
    );

    /**
     *
     * Create a new billing account that is used to charge Landlords for our service.
     *
     * @param AccountData $accountData Mapped BillingAccount.
     * @param Landlord $landlord
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
}
