<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use CreditJeeves\DataBundle\Entity\Operation as EntityOperation;
use CreditJeeves\DataBundle\Enum\OperationType;
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
    protected function isDuplicate($paidFor)
    {
        $operation = $this->em->getRepository('DataBundle:Operation')->getOperationForImport(
            $this->currentImportModel->getTenant(),
            $this->currentImportModel->getContract(),
            $paidFor
        );

        $payment = $this->currentImportModel->getContract()->getActivePayment();

        //We can't create double payment for current month
        if ($operation || !empty($payment)) {
            return true;
        }

        return false;
    }

    protected function getOperationByPaidFor($paidFor)
    {
        if ($this->isDuplicate($paidFor)) {
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
