<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use CreditJeeves\DataBundle\Entity\Operation as EntityOperation;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;

trait Operation
{
    /**
     * @param Contract $contract
     * @param $paidFor
     * @param $amount
     * @return bool
     */
    protected function isDuplicate(EntityContract $contract, $paidFor, $amount)
    {
        $operation = $this->em->getRepository('DataBundle:Operation')->getOperationForImport(
            $contract->getTenant(),
            $contract,
            $paidFor,
            $amount
        );

        //We can't create double payment for current month
        if ($operation) {
            return true;
        }

        return false;
    }

    /**
     * @param Import $import
     * @param $row
     *
     * @return Operation|null
     */
    protected function getOperationByRow(ModelImport $import, array $row)
    {
        if (!$import->isIsHasPaymentMapping()) {
            return null;
        }

        $contract = $import->getContract();
        if ($contract->getStatus() !== ContractStatus::CURRENT) {
            return null;
        }

        $amount = $row[Mapping::KEY_PAYMENT_AMOUNT];
        $paidFor = $this->getDateByField($import, $row[Mapping::KEY_PAYMENT_DATE]);

        if ($paidFor instanceof DateTime && $amount > 0 && $this->isDuplicate($contract, $paidFor, $amount)) {
            return null;
        }

        $operation = new EntityOperation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($amount);
        $operation->setType(OperationType::RENT);

        return $operation;
    }

    protected function getOperationByContract(EntityContract $contract, ModelImport $import, $paidFor)
    {
        if ($this->isDuplicate($contract, $paidFor, $contract->getRent())) {
            return null;
        }

        $operation = new EntityOperation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($contract->getRent());
        $operation->setType(OperationType::RENT);

        $import->setOperation($operation);

        return $operation;
    }
}
