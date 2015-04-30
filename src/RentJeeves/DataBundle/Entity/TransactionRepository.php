<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use DateTime;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;

class TransactionRepository extends EntityRepository
{
    /**
     * @param  Group    $group
     * @param  DateTime $date
     * @return mixed
     */
    public function getBatchDepositedInfo(Group $group, DateTime $date)
    {
        $query = $this->createQueryBuilder('h');
        $query->select(
            "h.batchId,
            h.transactionId,
            o.sum as amount,
            date_format(h.createdAt, '%m/%d/%Y') as dateInitiated,
            o.type as paymentType,
            o.status as orderStatus,
            h.status as transactionStatus,
            CONCAT_WS(' ', ten.first_name, ten.last_name) as resident,
            CONCAT_WS(' ', prop.number, prop.street) as property,
            prop.isSingle,
            unit.name as unitName"
        );
        $query->orderBy('h.batchId', 'DESC');
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('t.unit', 'unit');

        $query->where('t.group = :group');
        $query->setParameter('group', $group);

        $query->andWhere('h.depositDate = DATE(:date)');
        $query->setParameter('date', $date);

        $query->andWhere('h.batchId IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');

        $query->andWhere('h.status = :transactionStatus');
        $query->setParameter('transactionStatus', TransactionStatus::COMPLETE);

        $query->andWhere('o.status in (:status)');
        $query->setParameter('status', [OrderStatus::COMPLETE, OrderStatus::RETURNED, OrderStatus::REFUNDED]);

        $query->groupBy('h.id'); // for show all transactions

        return $query->getQuery()->execute();
    }

    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection $groups
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getTransactionsForRentTrackReport($groups, $start, $end, $exportBy)
    {
        $query = $this->createQueryBuilder('h');

        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->leftJoin('ten.residentsMapping', 'res');
        $query->innerJoin('t.unit', 'unit');
        $query->leftJoin('unit.unitMapping', 'uMap');
        $query->innerJoin('t.group', 'g');
        $query->leftJoin('g.groupSettings', 'gs');
        // order may be deposited and returned the same day, so we should count complete and reversal types
        $query->where('o.status in (:statuses)');
        if ($exportBy === ExportReport::EXPORT_BY_DEPOSITS) {
            $query->andWhere(
                '(o.status = :completeOrder AND h.status = :completeTransaction) OR
                (o.status != :completeOrder AND h.status = :reversedTransaction)'
            );

            $query->andWhere('h.isSuccessful = 1 AND h.transactionId IS NOT NULL AND h.depositDate IS NOT NULL');
            $query->andWhere("h.depositDate BETWEEN :start AND :end");
            $query->setParameter(
                'statuses',
                [
                    OrderStatus::COMPLETE,
                    OrderStatus::REFUNDED,
                    OrderStatus::RETURNED
                ]
            );
        } else {
            $query->andWhere(
                '((o.status = :completeOrder OR o.status = :pendingOrder) AND h.status = :completeTransaction) OR
                (o.status != :completeOrder AND h.status = :reversedTransaction)'
            );
            $query->setParameter('pendingOrder', OrderStatus::PENDING);
            $query->andWhere("o.created_at BETWEEN :start AND :end");
            $query->setParameter(
                'statuses',
                [
                    OrderStatus::COMPLETE,
                    OrderStatus::REFUNDED,
                    OrderStatus::RETURNED,
                    OrderStatus::PENDING
                ]
            );
        }

        $query->setParameter('completeTransaction', TransactionStatus::COMPLETE);
        $query->setParameter('reversedTransaction', TransactionStatus::REVERSED);
        $query->setParameter('completeOrder', OrderStatus::COMPLETE);

        $query->setParameter('start', $start);
        $query->setParameter('end', $end);

        $query->andWhere('o.type in (:paymentTypes)');
        $query->setParameter('paymentTypes', [OrderType::HEARTLAND_CARD, OrderType::HEARTLAND_BANK]);

        $query->andWhere('g.id in (:groups)');
        $query->setParameter('groups', $this->getGroupIds($groups));

        $query->orderBy('h.createdAt', 'ASC');

        $query->groupBy('h.id'); // for show all transactions

        return $query->getQuery()->execute();
    }
    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection $groups
     * @return array
     */
    protected function getGroupIds($groups)
    {
        $groupIds = [];
        foreach ($groups as $group) {
            /** @var Group $group */
            $groupIds[] = $group->getId();
        }

        return $groupIds;
    }

    public function getReversalDepositedInfo(Group $group, DateTime $date)
    {
        $query = $this->createQueryBuilder('h');
        $query->select(
            "h.transactionId,
            o.sum as amount,
            date_format(h.createdAt, '%m/%d/%Y') as reversalDate,
            date_format(o.created_at, '%m/%d/%Y') as originDate,
            o.type as paymentType,
            o.status as orderStatus,
            h.status as transactionStatus,
            h.messages,
            CONCAT_WS(' ', ten.first_name, ten.last_name) as resident,
            CONCAT_WS(' ', prop.number, prop.street) as property,
            prop.isSingle,
            unit.name as unitName"
        );

        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('t.unit', 'unit');

        $query->where('t.group = :group');
        $query->setParameter('group', $group);

        $query->andWhere('h.depositDate = DATE(:date)');
        $query->setParameter('date', $date);

        $query->andWhere('h.isSuccessful = 1');

        $query->andWhere('h.status = :transactionStatus');
        $query->setParameter('transactionStatus', TransactionStatus::REVERSED);

        $query->andWhere('o.status in (:statuses)');
        $query->setParameter('statuses', array(OrderStatus::REFUNDED, OrderStatus::RETURNED));

        $query->groupBy('h.id'); // for show all transactions

        return $query->getQuery()->execute();
    }

    public function getCountDeposits(Group $group, $accountType)
    {
        $query = $this->createQueryBuilder('h');
        $query->select('IF(h.batchId is null, h.depositDate, h.batchId) as batch');
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->where('t.group = :group');
        $query->andWhere('h.depositDate IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');

        $query->setParameter('group', $group);
        $query->groupBy('batch');

        if ($accountType) {
            $query->andWhere('o.type = :type');
            $query->setParameter('type', $accountType);
        }

        $query = $query->getQuery();

        return count($query->getScalarResult());
    }

    /**
     * TODO: get result without using Orders repository
     */
    public function getDepositedOrders(Group $group, $accountType, OrderRepository $ordersRepo, $page = 1, $limit = 100)
    {
        // get Batch Ids
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('h');
        $query->select(
            "IF(h.batchId is null, h.depositDate, h.batchId) as batch, sum(p.amount) as order_amount, h.depositDate"
        );
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        $query->andWhere('h.depositDate IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');
        if ($accountType) {
            $query->andWhere('o.type = :type');
            $query->setParameter('type', $accountType);
        }
        $query->groupBy('batch');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->orderBy('h.depositDate', 'DESC');
        $query = $query->getQuery();
        $deposits = $query->getScalarResult();

        foreach ($deposits as $key => $deposit) {
            $batchId = is_numeric($deposit['batch']) ? $deposit['batch'] : null;

            $ordersQuery = $ordersRepo->getDepositedOrdersQuery(
                $group,
                $accountType,
                $batchId,
                $deposit['depositDate']
            );

            $deposits[$key]['orders'] = $ordersQuery->getQuery()->execute();
            $depositDate = new DateTime($deposit['depositDate']);
            $deposits[$key]['depositDate'] = $depositDate->format('m/d/Y');
            $deposits[$key]['isDeposit'] = $batchId ? true : false;
        }

        return $deposits;
    }

    /**
     * This function supposes that for one batch_id exists only one merchant
     * @param $batchId
     * @return Holding|null
     */
    public function getMerchantHoldingByBatchId($batchId)
    {
        /** @var Transaction $transaction */
        $transaction = $this
            ->createQueryBuilder('h')
            ->where('h.batchId = :batchId')
            ->setParameter('batchId', $batchId)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        if ($transaction && $transaction->getContract()) {
            return $transaction->getContract()->getHolding();
        }

        return null;
    }
}
