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
        $query = $this->createQueryBuilder('pbm');
        return !!$query
            ->select('count(pbm.id)')
            ->where('pmb.paymentBatchId = :paymentBatchId')
            ->andWhere('pmb.paymentProcessor = :paymentProcessor')
            ->andWhere('pmb.accountingPackageType = :accountingPackageType')
            ->andWhere($query->expr()->isNull('pbm.accountingBatchId'))
            ->setParameters([
                'paymentBatchId' => $paymentBatchId,
                'paymentProcessor' => $paymentProcessor,
                'accountingPackageType' => $accountingPackageType
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
