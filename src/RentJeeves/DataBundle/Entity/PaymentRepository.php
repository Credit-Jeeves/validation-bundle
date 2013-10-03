<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;

/**
 * 
 * @author Alex Emelyanov
 * Aliases for this class
 * p - payment, table rj_payment, class Payment
 * c - contract, table rj_contract, class Contract
 * t - tenant, table cj_user, class Tenant
 * g - group, table cj_account_group, class Group
 *
 */
class PaymentRepository extends EntityRepository
{
    /**
     * @param array $days
     * @param int $month
     * @param int $year
     * @param PaymentType $type
     * @param PaymentStatus $status
     *
     * @fixme add joins
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getActivePayments(
        $days = array(),
        $month = 1,
        $year = 2000,
        $type = PaymentType::RECURRING,
        $status = PaymentStatus::ACTIVE
    ) {
        $query = $this->createQueryBuilder('p');
        $query->select('p, c');
        $query->innerJoin('p.contract', 'c');
        $query->where('p.status = :status');
        $query->andWhere('p.type = :type');
        $query->andWhere('p.dueDate IN (:days)');
        $query->andWhere('p.startMonth <= :month');
        $query->andWhere('p.endMonth >= :month');
        $query->andWhere('p.startYear <= :year');
        $query->andWhere('p.endYear >= :year');

        $query->setParameter('status', $status);
        $query->setParameter('type', $type);
        $query->setParameter('days', $days);
        $query->setParameter('month', $month);
        $query->setParameter('year', $year);

        $query = $query->getQuery();
        return $query->iterate();
    }
}
