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

    protected function getOperationByContract(EntityContract $contract, $paidFor)
    {
        if ($this->isDuplicate($contract, $paidFor, $contract->getRent())) {
            return null;
        }

        $operation = new EntityOperation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($contract->getRent());
        $operation->setType(OperationType::RENT);
        $operation->setCreatedAt($paidFor);

        return $operation;
    }
}
