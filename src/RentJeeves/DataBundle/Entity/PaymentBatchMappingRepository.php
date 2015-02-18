<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use \DateTime;

class PaymentBatchMappingRepository extends EntityRepository
{
    /**
     * @param $paymentBatchId
     * @param $accountingPackageType
     * @param $externalPropertyId
     * @return bool
     */
    public function isOpenedBatch($paymentBatchId, $accountingPackageType, $externalPropertyId)
    {
        return !!$this->createQueryBuilder('pbm')
            ->select('count(pbm.id)')
            ->where('pbm.paymentBatchId = :paymentBatchId')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->andWhere('pbm.externalPropertyId = :externalPropertyId')
            ->andWhere('pbm.status = :status')
            ->setParameters([
                'paymentBatchId' => $paymentBatchId,
                'accountingPackageType' => $accountingPackageType,
                'externalPropertyId' => $externalPropertyId,
                'status' => PaymentBatchStatus::OPENED,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $paymentBatchId
     * @param $accountingPackageType
     * @param $externalPropertyId
     * @return mixed
     */
    public function getAccountingBatchId($paymentBatchId, $accountingPackageType, $externalPropertyId)
    {
        return $this->createQueryBuilder('pbm')
            ->select('pbm.accountingBatchId')
            ->where('pbm.paymentBatchId = :paymentBatchId')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->andWhere('pbm.externalPropertyId = :externalPropertyId')
            ->andWhere('pbm.status = :status')
            ->setParameters([
                'paymentBatchId' => $paymentBatchId,
                'accountingPackageType' => $accountingPackageType,
                'externalPropertyId' => $externalPropertyId,
                'status' => PaymentBatchStatus::OPENED,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getYesterdayBatches($accountingPackageType)
    {
        $yesterday = new DateTime();
        $yesterday->modify("-1 day");

        return $this->createQueryBuilder('pbm')
            ->select('pbm.accountingBatchId')
            ->where('pbm.paymentBatchId = :paymentBatchId')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->andWhere(
                "STR_TO_DATE(pbm.openedAt,'%Y-%c-%e %T') = STR_TO_DATE(:openedAt,'%Y-%c-%e')"
            )
            ->andWhere('pbm.status = :status')
            ->setParameters([
                'accountingPackageType' => $accountingPackageType,
                'openedAt' => $yesterday->format('Y-m-d'),
                'status' => PaymentBatchStatus::OPENED,
            ])
            ->getQuery()->getResult();
    }
}
