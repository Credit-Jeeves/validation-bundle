<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;

interface CheckScanPaymentProcessorInterface extends PaymentProcessorInterface
{
    /**
     * @param Contract $contract
     * @param DepositAccount $depositAccount
     */
    public function registerContract(Contract $contract, DepositAccount $depositAccount);

    /**
     * @param Group $group
     * @param \DateTime $date
     * @return int
     */
    public function loadScannedChecks(Group $group, \DateTime $date);
}
