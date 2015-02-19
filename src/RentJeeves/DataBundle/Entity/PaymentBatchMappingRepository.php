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

    public function getTodayBatches($accountingPackageType)
    {
        $today = new DateTime();
        $query = $this->createQueryBuilder('pbm')
            ->where('pbm.status = :status')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->andWhere(
                "DATE_FORMAT(STR_TO_DATE(pbm.openedAt,'%Y-%c-%e %T'), '%Y-%c-%e') = STR_TO_DATE(:openedAt,'%Y-%c-%e')"
            )
            ->setParameters([
                'accountingPackageType' => $accountingPackageType,
                'openedAt' => $today->format('Y-m-d'),
                'status' => PaymentBatchStatus::OPENED,
            ])
            ->getQuery();

        return $query->getResult();
    }
}
