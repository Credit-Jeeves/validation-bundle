<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as AccountData;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface SubmerchantProcessorInterface
{
    /**
     * Creates a new payment account for User
     * Returns payment account token.
     *
     * @param  AccountData $data
     * @param  Contract           $contract
     * @return string
     */
    public function createPaymentToken(AccountData $data, Contract $contract);

    /**
     *
     * Create a new billing account token that we use to charge Landlords for our service.
     * Returns billing account token.
     *
     * @param AccountData $data
     * @param Landlord $user
     * @return mixed
     */
    public function createBillingToken(AccountData $data, Landlord $user);

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
