<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use \Doctrine_Expression;
use \DateTime;

/**
 * @author Alex Emelyanov
 *
 * Aliases for this class:
 * p - payment, table rj_payment, class Payment
 * c - contract, table rj_contract, class Contract
 * t - tenant, table cj_user, class Tenant
 * g - group, table cj_account_group, class Group
 * j - Job
 *
 */
class PaymentRepository extends EntityRepository
{

    /**
     * @param array $days
     * @param int $month
     * @param int $year
     * @param array $ids
     *
     * @return ArrayCollection
     */
    public function getActivePayments(
        $days = array(),
        $month = 1,
        $year = 2000,
        array $ids = array()
    ) {
        $query = $this->createQueryBuilder('p');
        $query->select("p, c, g, d");
        $query->innerJoin('p.contract', 'c');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('g.deposit_account', 'd');
        $query->leftJoin(
            'p.jobs',
            'j',
            Expr\Join::WITH,
            "DATE(j.createdAt) > :monthBefor"
        );
        $query->andWhere('p.status = :status');
        $query->andWhere('p.dueDate IN (:days)');
        $query->andWhere('j.id IS NULL');
        $query->andWhere(
            sprintf(
                "STR_TO_DATE(" .
                "CONCAT(%s.startYear, '-', %s.startMonth, '-', %s.dueDate)," .
                "'%%Y-%%c-%%e'" .
                ") <= :startDate",
                'p',
                'p',
                'p'
            )
        );
        $query->andWhere(
            '(p.endYear IS NULL AND p.endMonth IS NULL)
            OR
            (p.endYear > :year)
            OR
            (p.endYear = :year AND p.endMonth >= :month)'
        );
        if (!empty($ids)) {
            $query->andWhere('p.id IN (:ids)');
            $query->setParameter('ids', $ids);
        }

        if (count($days) === 1) {
            $day = array_values($days)[0];
        } else {
            $day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        }
        $date = new DateTime(implode('-', array($year, $month, $day)));
        $monthBefor = clone $date;
        $monthBefor->modify('-1 month');
        $query->setParameter('status', PaymentStatus::ACTIVE);
        $query->setParameter('days', $days);
//        $query->setParameter('contract', $contract);
        $query->setParameter('month', $month);
        $query->setParameter('year', $year);
        $query->setParameter('monthBefor', $monthBefor->format('Y-m-d'));
        $query->setParameter('startDate', implode('-', array($year, $month, $day)));

        $query = $query->getQuery();
//        echo $query->getSQL();die('OK');
        return $query->execute();
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
