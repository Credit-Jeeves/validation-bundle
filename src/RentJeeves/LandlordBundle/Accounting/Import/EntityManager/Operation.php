<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use CreditJeeves\DataBundle\Entity\Operation as EntityOperation;
use CreditJeeves\DataBundle\Enum\OperationType;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract as EntityContract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import as ModelImport;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @property Import currentImportModel
 */
trait Operation
{
    /**
     * @param Contract $contract
     * @param $paidFor
     * @param $amount
     * @return bool
     */
    protected function isDuplicate($paidFor, $amount)
    {
        $operation = $this->em->getRepository('DataBundle:Operation')->getOperationForImport(
            $this->currentImportModel->getTenant(),
            $this->currentImportModel->getContract(),
            $paidFor,
            $amount
        );

        //We can't create double payment for current month
        if ($operation) {
            return true;
        }

        return false;
    }

    protected function getOperationByPaidFor($paidFor)
    {
        if ($this->isDuplicate($paidFor, $this->currentImportModel->getContract()->getRent())) {
            return null;
        }

        $operation = new EntityOperation();
        $operation->setPaidFor($paidFor);
        $operation->setAmount($this->currentImportModel->getContract()->getRent());
        $operation->setType(OperationType::RENT);
        $operation->setCreatedAt($paidFor);

        return $operation;
    }
}
