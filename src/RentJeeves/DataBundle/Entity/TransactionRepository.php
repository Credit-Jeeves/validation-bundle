<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Entity\Group;
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
            o.paymentType as paymentType,
            o.status as orderStatus,
            d.accountNumber as accountNumber,
            d.type as depositAccountType,
            h.status as transactionStatus,
            CONCAT_WS(' ', ten.first_name, ten.last_name) as resident,
            CONCAT_WS(' ', propertyAddress.number, propertyAddress.street) as property,
            prop.isSingle,
            unit.name as unitName"
        );
        $query->orderBy('h.batchId', 'DESC');
        $query->innerJoin('h.order', 'o');
        $query->leftJoin('o.depositAccount', 'd');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('prop.propertyAddress', 'propertyAddress');
        $query->leftJoin('t.unit', 'unit');

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

        $query->andWhere('o.paymentType in (:paymentTypes)');
        $query->setParameter('paymentTypes', [OrderPaymentType::CARD, OrderPaymentType::BANK]);

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
            h.batchId,
            o.sum as amount,
            date_format(h.createdAt, '%m/%d/%Y') as reversalDate,
            date_format(o.created_at, '%m/%d/%Y') as originDate,
            o.paymentType as paymentType,
            o.status as orderStatus,
            h.status as transactionStatus,
            h.messages,
            CONCAT_WS(' ', ten.first_name, ten.last_name) as resident,
            CONCAT_WS(' ', propertyAddress.number, propertyAddress.street) as property,
            prop.isSingle,
            unit.name as unitName"
        );

        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('prop.propertyAddress', 'propertyAddress');
        $query->leftJoin('t.unit', 'unit');

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

    /**
     * @param Group $group
     * @param string $filter
     * @param string $search
     * @return int
     */
    public function getCountDeposits(Group $group, $filter, $search)
    {
        $query = $this->createQueryBuilder('h')
            ->select('IF(h.batchId is null, h.depositDate, h.batchId) as batch')
            ->innerJoin('h.order', 'o')
            ->innerJoin('o.operations', 'p')
            ->innerJoin('o.depositAccount', 'da')
            ->innerJoin('p.contract', 't')
            ->where('t.group = :group')
            ->andWhere('h.depositDate IS NOT NULL')
            ->andWhere('h.isSuccessful = 1')
            ->setParameter('group', $group)
            ->groupBy('batch');

        if (!empty($filter) && !empty($search)) {
            $query->andWhere('h.' . $filter . ' = :search');
            $query->setParameter('search', $search);
        }

        return count($query->getQuery()->getScalarResult());
    }

    /**
     * @param Group $group
     * @param string $filter
     * @param string $search
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getBatchedDeposits(Group $group, $filter, $search, $page = 1, $limit = 100)
    {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('h');
        $query->select(
            'h.batchId batchNumber, sum(p.amount) orderAmount, h.depositDate, da.type depositType, h.status'
        );
        $query->innerJoin('h.order', 'o');
        $query->innerJoin('o.depositAccount', 'da');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        $query->andWhere('h.depositDate IS NOT NULL');
        $query->andWhere('h.isSuccessful = 1');
        if (!empty($filter) && !empty($search)) {
            $query->andWhere('h.' . $filter . ' = :search');
            $query->setParameter('search', $search);
        }
        $query->groupBy('batchNumber');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->orderBy('h.depositDate', 'DESC');
        $query = $query->getQuery();

        return $query->getScalarResult();
    }

    /**
     * @param string $batchId
     * @return mixed
     */
    public function getTransactionsForBatch($batchId)
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.order', 'o')
            ->where('h.batchId = :batch')
            ->andWhere('h.isSuccessful = 1')
            ->andWhere('h.depositDate IS NOT NULL')
            ->setParameter('batch', $batchId)
            ->getQuery()
            ->execute();
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
