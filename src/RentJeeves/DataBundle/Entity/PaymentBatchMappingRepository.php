<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;

class PaymentBatchMappingRepository extends EntityRepository
{
    /**
     * @param $paymentBatchId
     * @param $paymentProcessor
     * @param $accountingPackageType
     * @return bool
     */
    public function isOpenedBatch($paymentBatchId, $paymentProcessor, $accountingPackageType)
    {
        return !!$this->createQueryBuilder('pbm')
            ->select('count(pbm.id)')
            ->where('pbm.paymentBatchId = :paymentBatchId')
            ->andWhere('pbm.paymentProcessor = :paymentProcessor')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->setParameters([
                'paymentBatchId' => $paymentBatchId,
                'paymentProcessor' => $paymentProcessor,
                'accountingPackageType' => $accountingPackageType
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAccountingBatchId($paymentBatchId, $paymentProcessor, $accountingPackageType)
    {
        return $this->createQueryBuilder('pbm')
            ->select('count(pbm.accountingBatchId)')
            ->where('pbm.paymentBatchId = :paymentBatchId')
            ->andWhere('pbm.paymentProcessor = :paymentProcessor')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->setParameters([
                'paymentBatchId' => $paymentBatchId,
                'paymentProcessor' => $paymentProcessor,
                'accountingPackageType' => $accountingPackageType,
                'status' => PaymentBatchStatus::OPENED
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
