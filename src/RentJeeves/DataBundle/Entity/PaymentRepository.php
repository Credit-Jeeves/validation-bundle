<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

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
        $query->innerJoin(
            'p.contract',
            'c',
            Expr\Join::WITH,
            "c.status NOT IN (:contractNotActiveStatuses)"
        );
        $query->setParameter('contractNotActiveStatuses', array(ContractStatus::DELETED, ContractStatus::FINISHED));
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('g.depositAccounts', 'd');
        $query->leftJoin(
            'p.jobs',
            'j',
            Expr\Join::WITH,
            "DATE(j.createdAt) = :startDate"
        );
        $query->andWhere('j.id IS NULL');
        $query->andWhere('p.status = :status');
        $query->andWhere('c.paymentAccepted = :paymentAccepted');
        $query->andWhere('p.dueDate IN (:days)');
        $query->andWhere(
            self::getStartDateDQLString('p') . ' <= :startDate'
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
        $query->setParameter('paymentAccepted', (string) PaymentAccepted::ANY);

        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @return array
     */
    public function collectToJobs(DateTime $date = null)
    {
        if (null === $date) {
            $date = new DateTime();
        }
        $payments = $this->getActivePayments($date);

        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $jobs = array();
        /** @var Payment $payment */
        foreach ($payments as $payment) {
            $em->persist($jobs[] = $payment->createJob());
        }
        if ($jobs) {
            $em->flush();
        }

        return $jobs;
    }

    /**
     * Converts fields: dueDate, startYear, startMonth to valid DATE object
     *
     * @param string $alias
     *
     * @return string
     */
    public static function getStartDateDQLString($alias)
    {
        return str_replace(
            '%alias',
            $alias,
            "STR_TO_DATE(
                CONCAT_WS(
                    '-',
                    %alias.startYear,
                    %alias.startMonth,
                    (CASE
                        WHEN (
                            DAY(LAST_DAY(STR_TO_DATE(
                                CONCAT_WS('-',%alias.startYear,%alias.startMonth,'1'),'%Y-%c-%e')
                            )) < %alias.dueDate
                        ) THEN (
                            DAY(LAST_DAY(STR_TO_DATE(
                                CONCAT_WS('-',%alias.startYear,%alias.startMonth,'1'),'%Y-%c-%e')
                            ))
                        ) ELSE (
                            %alias.dueDate
                        )
                    END)
                ),
                '%Y-%c-%e'
            )"
        );
    }

    /**
     * @param int $id
     *
     * @return Payment | null
     */
    public function findOneWithContractOrdersOperations($id)
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.contract', 'c');
        $query->andWhere('p.id = :id');
        $query->setParameter('id', $id);
        $query = ContractRepository::queryOperationsOrdersHistory($query)->getQuery();

        return $query->getOneOrNullResult();

    }

    /**
     * @param int $id
     * @param User $user
     *
     * @return Payment | null
     */
    public function findOneByIdForUser($id, $user)
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.paymentAccount', 'pa');
        $query->andWhere('p.id = :id');
        $query->andWhere('pa.user = :user');
        $query->setParameter('id', $id);
        $query->setParameter('user', $user);

        return $query->getQuery()->getOneOrNullResult();

    }

    /**
     * @param User $user
     *
     * @return Array
     */
    public function findByUser($user)
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.paymentAccount', 'pa');
        $query->andWhere('pa.user = :user');
        $query->setParameter('user', $user);
        $query->orderBy('p.paidFor', 'DESC');

        return $query->getQuery()->execute();
    }
}
