<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * 
 * @author Alex Emelyanov
 * Aliases for this class
 * p - payment, table rj_payment, class Payment
 * c - contract, table rj_contract, class Contract
 * t - tenant, table cj_user, class Tenant
 * g - group, table cj_account_group, class Group
 * oper - Operation
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
        $contract = array(ContractStatus::APPROVED, ContractStatus::CURRENT),
        $types = array(PaymentType::RECURRING)
    ) {
        $query = $this->createQueryBuilder('p');
        $query->select('p, c, g, d');
        $query->innerJoin('p.contract', 'c');
        $query->innerJoin('c.group', 'g');
        $query->leftJoin('c.operation', 'oper');
        $query->innerJoin('g.deposit_account', 'd');
        $query->where('p.status = :status');
        $query->andWhere('p.type IN (:type)');
        $query->andWhere('p.dueDate IN (:days)');
        $query->andWhere('c.status IN (:contract)');
        $query->andWhere('p.startMonth <= :month');
        $query->andWhere('p.startYear <= :year');
        $query->andWhere('p.endYear IS NULL OR (p.endYear > :year) OR (p.endYear = :year AND p.endMonth >= :month)');

        $query->setParameter('status', PaymentStatus::ACTIVE);
        $query->setParameter('type', $types);
        $query->setParameter('days', $days);
        $query->setParameter('contract', $contract);
        $query->setParameter('month', $month);
        $query->setParameter('year', $year);

        $query = $query->getQuery();
        return $query->iterate();
    }

    public function getNonAutoPayments(
        $days = array(),
        $month = 1,
        $year = 2000,
        $types = array(PaymentType::ONE_TIME, PaymentType::IMMEDIATE),
        $statuses = array(PaymentStatus::PAUSE, PaymentStatus::CLOSE),
        $contract = array(ContractStatus::APPROVED, ContractStatus::CURRENT)
    ) {
        $query = $this->createQueryBuilder('p');
        $query->select('p, c');
        $query->innerJoin('p.contract', 'c');
        $query->where('p.status IN (:status)');
        $query->andWhere('p.type IN (:type)');
        $query->andWhere('p.dueDate IN (:days)');
        $query->andWhere('c.status IN (:contract)');
        $query->andWhere('p.startMonth <= :month');
        $query->andWhere('p.startYear <= :year');
        $query->andWhere('p.endYear IS NULL OR (p.endYear > :year) OR (p.endYear = :year AND p.endMonth >= :month)');

        $query->setParameter('status', $statuses);
        $query->setParameter('type', $types);
        $query->setParameter('days', $days);
        $query->setParameter('contract', $contract);
        $query->setParameter('month', $month);
        $query->setParameter('year', $year);
    
        $query = $query->getQuery();
        return $query->iterate();
    }
}
