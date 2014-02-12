<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use RentJeeves\CoreBundle\Traits\DateCommon;
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
    use DateCommon;

    /**
     * @param DateTime $date
     * @param array $ids
     *
     * @return ArrayCollection
     */
    public function getActivePayments(
        DateTime $date,
        array $ids = array()
    ) {
        $month = $date->format('n');
        $year = $date->format('Y');
        $days = $this->getDueDays(0, $date);

        $query = $this->createQueryBuilder('p');
        $query->select("p, c, g, d");
        $query->innerJoin('p.contract', 'c');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('g.deposit_account', 'd');
        $query->leftJoin(
            'p.jobs',
            'j',
            Expr\Join::WITH,
            "DATE(j.createdAt) <> :startDate"
        );
        $query->andWhere('p.status = :status');
        $query->andWhere('p.dueDate IN (:days)');
        $query->andWhere('j.id IS NULL');
        $query->andWhere(
            "STR_TO_DATE(" .
            "CONCAT_WS('-', p.startYear, p.startMonth, p.dueDate)," .
            "'%%Y-%%c-%%e'" .
            ") <= :startDate"
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
        $query->setParameter('status', PaymentStatus::ACTIVE);
        $query->setParameter('days', $days);
        $query->setParameter('month', $month);
        $query->setParameter('year', $year);
        $query->setParameter('startDate', $date->format('Y-m-d'));

        $query = $query->getQuery();
        var_dump(getDqlWithParams($query));die('OK');
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

    /**
     * @return array
     */
    public function collectToJobs()
    {
        $payments = $this->getActivePayments(new DateTime());

        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $jobs = array();
        /** @var Payment $payment */
        foreach ($payments as $payment) {
            $em->persist($jobs[] = $payment->createJob());
        }
        $em->flush();
        return $jobs;
    }
}
