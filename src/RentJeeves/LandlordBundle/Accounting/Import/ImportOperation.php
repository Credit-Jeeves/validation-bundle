<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;

trait ImportOperation
{
    /**
     * @param Import $import
     * @param $row
     *
     * @return Operation|null
     */
    protected function getOperation(ModelImport $import, array $row)
    {
        if (!$this->mapping->hasPaymentMapping($row)) {
            return null;
        }

        $contract = $import->getContract();
        if ($contract->getStatus() !== ContractStatus::CURRENT) {
            return null;
        }

        $tenant = $import->getTenant();
        $amount = $row[ImportMapping::KEY_PAYMENT_AMOUNT];
        $paidFor = $this->getDateByField($row[ImportMapping::KEY_PAYMENT_DATE]);

        if ($paidFor instanceof DateTime && $amount > 0) {
            $operation = $this->em->getRepository('DataBundle:Operation')->getOperationForImport(
                $tenant,
                $contract,
                $paidFor,
                $amount
            );

            //We can't create double payment for current month
            if ($operation) {
                return null;
            }
        }

        $operation = new Operation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($amount);
        $operation->setType(OperationType::RENT);

        return $operation;
    }
} 