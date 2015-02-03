<?php
namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\Payment;

interface PaymentProcessorInterface
{
    public function createDepositAccount(Group $group);
    public function createPaymentAccount(PaymentAccountData $data, User $user, Group $group);
    public function executePayment(Payment $payment);
    public function processDepositReport(DateTime $date);
    public function processReversalReport(DateTime $date);
}
