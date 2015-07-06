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
     * Function lock table for entity PaymentBatchMapping for READ and WRITE
     * We use alias r0_, because Doctrine2 generate alias for tables and MySQL can't work without alias
     */
    public function lockTable()
    {
        $this->getEntityManager()->getConnection()->exec(
            sprintf(
                'LOCK TABLES %1$s as r0_ READ, %1$s WRITE;',
                $this->getClassMetadata()->getTableName()
            )
        );
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
