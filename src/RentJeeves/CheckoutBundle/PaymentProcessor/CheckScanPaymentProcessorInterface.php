<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;

interface CheckScanPaymentProcessorInterface extends PaymentProcessorInterface
{
    /**
     * @param Contract $contract
     * @param DepositAccount $depositAccount
     */
    public function registerContract(Contract $contract, DepositAccount $depositAccount);
}
