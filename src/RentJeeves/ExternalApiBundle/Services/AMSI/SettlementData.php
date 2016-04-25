<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Model\Holding;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\DataBundle\Enum\TransactionStatus;

/**
 * @DI\Service("accounting.amsi_settlement")
 */
class SettlementData
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager")
     * })
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns an array of [batchId, amount, groupId, depositDate, batchDate].
     * Batch is closed the same day as it was created - so orders can be in COMPLETE or PENDING state.
     *
     * @param  \DateTime $date
     * @param  Holding   $holding
     * @return array
     */
    public function getBatchesToClose(\DateTime $date, Holding $holding)
    {
        $formattedDate = $date->format('Y-m-d');

        // To avoid duplications of order amounts when joining multiple operations, we use a subquery to get groupId

        // subquery
        $qb = $this->em->getRepository('DataBundle:Operation')->createQueryBuilder('op')
            ->select('g.id')
            ->join('op.contract', 'c')
            ->join('c.group', 'g')
            ->where('op.order = o')
            ->groupBy('op.order');

        // main query
        $query = $this->em->getRepository('RjDataBundle:Transaction')->createQueryBuilder('tr')
            ->select('tr.batchId AS batchId, SUM(o.sum) AS amount, tr.depositDate, tr.batchDate')
            ->addSelect(sprintf('(%s) as groupId', $qb->getDQL()))
            ->join('tr.order', 'o')
            ->where('date(o.created_at) = :date')
            ->andWhere('o.status in (:orderStatuses)')
            ->andWhere('tr.isSuccessful = :isSuccessful')
            ->andWhere('tr.status = :completeTransaction')
            ->andWhere('tr.batchId IS NOT NULL')
            ->groupBy('batchId')
            ->having('groupId in (:groupIds)')
            ->setParameter('date', $formattedDate)
            ->setParameter('isSuccessful', true)
            ->setParameter('orderStatuses', [OrderStatus::COMPLETE, OrderStatus::PENDING])
            ->setParameter('completeTransaction', TransactionStatus::COMPLETE)
            ->setParameter('groupIds', $this->getGroupIds($holding))
            ->getQuery();

        $result = $query->execute();

        return $result;
    }

    /**
     * @param  Holding $holding
     * @return array
     */
    protected function getGroupIds(Holding $holding)
    {
        $groups = $this->em->getRepository('DataBundle:Group')->getAllGroupIdsInHolding($holding);

        $result = array_map(function ($group) {
            return $group['id'];
        }, $groups);

        return $result;
    }

    /**
     * @param \DateTime $batchDate
     * @param \DateTime $depositDate
     *
     * @return \DateTime
     */
    public function getSettlementDate(\DateTime $batchDate = null, \DateTime $depositDate = null)
    {
        if ($depositDate) {
            return $depositDate;
        }

        $date = new \DateTime();
        if ($batchDate) {
            $date = $batchDate;
        }

        return BusinessDaysCalculator::getDepositDate($date, 3); // Amount of days to get payment deposited = 3
    }
}
