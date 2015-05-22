<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ExternalApi;
use Doctrine\ORM\Query\Expr;
use DateTime;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * Aliases for this class
 * o - Order
 * p - payment, table rj_payment, class Payment
 * c - contract, table rj_contract, class Contract
 * t - tenant, table cj_user, class Tenant
 * g - group, table cj_account_group, class Group
 * oper - Operation
 * prop - Property
 * unit - Unit
 *
 */
class OrderRepository extends EntityRepository
{
    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\User $User
     */
    public function deleteUserOrders(\CreditJeeves\DataBundle\Entity\User $User)
    {
        $query = $this->createQueryBuilder('o')
            ->delete()
            ->where('o.cj_applicant_id = :id')
            ->setParameter('id', $User->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @param  Group $group
     * @param  string $searchBy
     * @param  string $search
     * @param  bool $showCashPayments
     * @return array
     */
    public function countOrders(
        \CreditJeeves\DataBundle\Entity\Group $group,
        $searchBy = '',
        $search = '',
        $showCashPayments = true
    ) {
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->leftJoin('t.unit', 'unit');
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search) && !empty($searchBy)) {
            $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy . ' LIKE :search');
                $query->setParameter('search', '%' . $item . '%');
            }
        }

        if (!$showCashPayments) {
            $query->andWhere('o.type != :cash');
            $query->setParameter('cash', OrderType::CASH);
        }

        $query->groupBy('o.id');
        $query = $query->getQuery();

        return $query->getScalarResult();
    }

    /**
     * @param  Group $group
     * @param  int $page
     * @param  int $limit
     * @param  string $sort
     * @param  string $order
     * @param  string $searchBy
     * @param  string $search
     * @param  bool $showCashPayments
     * @return mixed
     */
    public function getOrdersPage(
        \CreditJeeves\DataBundle\Entity\Group $group,
        $page = 1,
        $limit = 100,
        $sort = 'o.status',
        $order = 'ASC',
        $searchBy = 'p.street',
        $search = '',
        $showCashPayments = true
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->leftJoin('t.unit', 'unit');
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        $query->groupBy('o.id');
        if (!empty($search) && !empty($searchBy)) {
            $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy . ' LIKE :search');
                $query->setParameter('search', '%' . $item . '%');
            }
        }
        switch ($sort) {
            case 'first_name':
                $query->orderBy('ten.first_name', $order);
                $query->addOrderBy('ten.last_name', $order);
                break;
            case 'date-posted':
                $query->orderBy('o.created_at', $order);
                break;
            case 'date-initiated':
                $query->orderBy('o.updated_at', $order);
                break;
            case 'property':
                $query->orderBy('prop.number', $order);
                $query->addOrderBy('prop.street', $order);
                $query->addOrderBy('unit.name', $order);
                break;
            default:
                $sort = 'o.' . $sort;
                $query->orderBy($sort, $order);
                break;
        }
        $this->applySortField($sort);
        if (!$showCashPayments) {
            $query->andWhere('o.type != :cash');
            $query->setParameter('cash', OrderType::CASH);
        }
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    private function applySearchField(&$field)
    {
        switch ($field) {
            case 'status':
                $field = 'o.' . $field;
                break;
            case 'amount':
                $field = 'o.sum';
                break;
            case 'property':
                $field = 'CONCAT(prop.street, prop.number)';
                break;
            case 'tenant':
                $field = 'CONCAT(ten.first_name, ten.last_name)';
                break;
            default:
                $field = 'o.status';
                break;
        }
    }

    private function applySortField(&$field)
    {
        switch ($field) {
            case 'status':
                $field = 'o.' . $field;
                break;
            case 'amount':
                $field = 'o.sum';
                break;
            case 'date-posted':
                $field = 'o.created_at';
                break;
            case 'date-initiated':
                $field = 'o.updated_at';
                break;
            case 'property':
                $field = 'prop.street';
                break;
            case 'tenant':
                $field = 'CONCAT(ten.first_name, ten.last_name)';
                break;
            case 'first_name':
                $field = 'ten.first_name';
                break;
            default:
                $field = 'o.status';
                break;
        }
    }

    /**
     * @param  string $search
     * @return array
     */
    private function prepareSearch($search)
    {
        $search = preg_replace('/\s+/', ' ', trim($search));
        $search = explode(' ', $search);

        return $search;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     */
    public function getContractHistory(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p', Expr\Join::WITH, "p.type = :rent");
        $query->setParameter('rent', OperationType::RENT);
        $query->where('p.contract = :contract');
        $query->setParameter('contract', $contract);
        $query->orderBy('o.created_at', 'ASC');
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     */
    public function getLastContractPayment(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->where('p.contract = :contract');
        $query->andWhere('o.status in (:status)');
        $query->setParameter('contract', $contract);
        $query->setParameter('status', array(OrderStatus::COMPLETE, OrderStatus::PENDING));
        $query->orderBy('o.created_at', 'DESC');
        $query->setMaxResults(1);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param string $start
     * @param string $end
     * @param array $groups
     * @param string $exportBy
     * @param Property $property
     * @return Order[]
     */
    public function getOrdersForYardiGenesis(
        $start,
        $end,
        array $groups,
        $exportBy,
        Property $property = null
    ) {
        return $this->getOrdersForRealPageReport($groups, $property, $start, $end, $exportBy);
    }

    /**
     * @param array $groups
     * @param Property $property
     * @param string $start
     * @param string $end
     * @param string $exportBy
     * @return mixed
     */
    public function getOrdersForRealPageReport(
        array $groups,
        Property $property = null,
        $start,
        $end,
        $exportBy
    ) {

        if (empty($groups)) {
            throw new \LogicException('Must have at least one group');
        }

        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.unit', 'unit');
        $query->innerJoin('t.group', 'g');
        $query->innerJoin('o.transactions', 'transaction');

        if ($exportBy === ExportReport::EXPORT_BY_DEPOSITS) {
            $query->where('transaction.isSuccessful = 1 AND transaction.depositDate IS NOT NULL');
            $query->andWhere("transaction.depositDate BETWEEN :start AND :end");
            $query->andWhere('o.status IN (:statuses)');
            $query->setParameter('statuses', [
                OrderStatus::COMPLETE,
                OrderStatus::REFUNDED,
                OrderStatus::RETURNED,
            ]);
        } else {
            $query->where("STR_TO_DATE(o.created_at, '%Y-%c-%e') BETWEEN :start AND :end");
            $query->andWhere('o.status IN (:statuses)');
            $query->setParameter('statuses', [
                OrderStatus::COMPLETE,
                OrderStatus::REFUNDED,
                OrderStatus::RETURNED,
                OrderStatus::PENDING
            ]);
        }

        $query->andWhere('g.id in (:groups)');

        if ($property !== null) {
            $query->innerJoin('t.property', 'prop');
            $query->andWhere('prop.id = :propId');
            $query->setParameter('propId', $property->getId());
        }

        $query->setParameter('end', $end);
        $query->setParameter('start', $start);

        $groupsId = [];
        foreach ($groups as $group) {
            $groupsId[] = $group->getId();
        }
        $query->setParameter('groups', $groupsId);

        $query->orderBy('o.id', 'ASC');
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param array $groups
     * @param string $start
     * @param string $end
     * @param string $exportBy
     * @return mixed
     */
    public function getOrdersForPromasReport(array $groups, $start, $end, $exportBy)
    {
        if (empty($groups)) {
            throw new \LogicException('Must have at least one group');
        }

        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('ten.residentsMapping', 'res');
        $query->innerJoin('t.unit', 'unit');
        $query->innerJoin('unit.unitMapping', 'uMap');
        $query->innerJoin('o.transactions', 'transaction');
        $query->innerJoin('t.group', 'g');
        $query->innerJoin('g.groupSettings', 'gs');

        if ($exportBy === ExportReport::EXPORT_BY_DEPOSITS) {
            $query->where('o.status IN (:statuses)');
            $query->andWhere('transaction.isSuccessful = 1 AND transaction.depositDate IS NOT NULL');
            $query->andWhere("transaction.depositDate BETWEEN :start AND :end");
            $query->setParameter('statuses', [OrderStatus::COMPLETE, OrderStatus::REFUNDED, OrderStatus::RETURNED]);
        } else {
            $query->where('o.status IN (:statuses)');
            $query->andWhere("STR_TO_DATE(o.created_at, '%Y-%c-%e') BETWEEN :start AND :end");
            $query->setParameter('statuses', [
                OrderStatus::COMPLETE,
                OrderStatus::REFUNDED,
                OrderStatus::RETURNED,
                OrderStatus::PENDING
            ]);
        }

        $query->andWhere('o.type in (:orderType)');
        $query->andWhere('g.id in (:groups)');
        $query->andWhere('gs.isIntegrated = 1');
        $query->andWhere('res.holding = :holding');
        $query->setParameter('end', $end);
        $query->setParameter('start', $start);

        $groupsId = [];
        foreach ($groups as $group) {
            $groupsId[] = $group->getId();
        }
        $query->setParameter('orderType', [OrderType::HEARTLAND_CARD, OrderType::HEARTLAND_BANK]);
        $query->setParameter('groups', $groups);
        $query->setParameter('holding', $group->getHolding());
        $query->orderBy('res.residentId', 'ASC');
        $query->orderBy('transaction.batchId', 'ASC');
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getDepositedOrdersQuery($group, $accountType, $batchId, $depositDate)
    {
        $ordersQuery = $this->createQueryBuilder('o');
        $ordersQuery->innerJoin('o.operations', 'p');
        $ordersQuery->innerJoin('p.contract', 't');
        $ordersQuery->innerJoin('o.transactions', 'h');
        $ordersQuery->where('t.group = :group');
        $ordersQuery->andWhere('h.depositDate IS NOT NULL');
        if ($batchId) {
            $ordersQuery->andWhere('h.batchId = :batchId');
            $ordersQuery->setParameter('batchId', $batchId);
        } else {
            $ordersQuery->andWhere('h.batchId is null');
            $ordersQuery->andWhere('h.depositDate = :depositDate');
            $ordersQuery->setParameter('depositDate', $depositDate);
        }

        $ordersQuery->setParameter('group', $group);
        if ($accountType) {
            $ordersQuery->andWhere('o.type = :type');
            $ordersQuery->setParameter('type', $accountType);
        }

        return $ordersQuery;
    }

    public function getTenantPayments(Tenant $tenant, $page = 1, $contractId = null, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('o');
        $query->where('o.user = :user');
        $query->orderBy('o.created_at', 'DESC');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->setParameter('user', $tenant);

        if ($contractId) {
            $query->innerJoin('o.operations', 'op');
            $query->innerJoin('op.contract', 'c');
            $query->andWhere('c.id = :contractId');
            $query->setParameter('contractId', $contractId);
        }

        return $query->getQuery()->execute();
    }

    public function getTenantPaymentsAmount(Tenant $tenant, $contractId = null)
    {
        $query = $this->createQueryBuilder('o');
        $query->select('count(distinct o.id)');
        $query->where('o.user = :user');
        $query->setParameter('user', $tenant);

        if ($contractId) {
            $query->innerJoin('o.operations', 'op');
            $query->innerJoin('op.contract', 'c');
            $query->andWhere('c.id = :contractId');
            $query->setParameter('contractId', $contractId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getBatchIds(
        DateTime $depositDate,
        Holding $holding,
        $start,
        $limit
    ) {
        $query = $this->getBaseReceiptBatchQuery($depositDate, $holding, $start, $limit);
        $query->groupBy("transaction.batchId");
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getReceiptBatch(
        DateTime $depositDate,
        Holding $holding,
        $batchId,
        $start,
        $limit
    ) {
        $query = $this->getBaseReceiptBatchQuery($depositDate, $holding, $start, $limit);
        $query->andWhere("transaction.batchId = :batchId");
        $query->setParameter('batchId', $batchId);

        $query = $query->getQuery();

        return $query->execute();
    }

    protected function getBaseReceiptBatchQuery(
        DateTime $depositDate,
        Holding $holding,
        $start,
        $limit
    ) {
        $query = $this->createQueryBuilder('ord');
        $query->innerJoin('ord.operations', 'operation');
        $query->innerJoin('operation.contract', 'contract');
        $query->innerJoin('contract.group', 'group');
        $query->innerJoin('group.holding', 'holding');
        $query->innerJoin('contract.property', 'property');
        $query->innerJoin('property.propertyMapping', 'mapping');
        $query->innerJoin('ord.transactions', 'transaction');
        $query->leftJoin(
            'ord.sentOrder',
            'externalApi',
            Expr\Join::WITH,
            'externalApi.depositDate = :depositDate1 AND externalApi.apiType = :apiType'
        );
        $query->where("transaction.depositDate = :depositDate2");
        $query->andWhere('externalApi.id IS NULL');
        $query->andWhere('transaction.isSuccessful = 1');
        $query->andWhere('mapping.externalPropertyId IS NOT NULL');
        $query->andWhere('ord.status = :orderStatus');
        $query->andWhere('operation.type = :rentStatus OR operation.type = :otherStatus');
        $query->andWhere('mapping.holding = :holdingId');

        $query->setParameter('apiType', ExternalApi::YARDI);
        $query->setParameter('depositDate1', $depositDate->format('Y-m-d'));
        $query->setParameter('depositDate2', $depositDate->format('Y-m-d'));
        $query->setParameter('orderStatus', OrderStatus::COMPLETE);
        $query->setParameter('rentStatus', OperationType::RENT);
        $query->setParameter('otherStatus', OperationType::OTHER);
        $query->setParameter('holdingId', $holding->getId());
        $query->setFirstResult($start);
        $query->setMaxResults($limit);

        return $query;
    }

    public function getReversedOrders(Holding $holding, DateTime $depositDate, $start, $limit)
    {
        $query = $this->createQueryBuilder('ord');
        $query->innerJoin('ord.operations', 'operation');
        $query->innerJoin('operation.contract', 'contract');
        $query->innerJoin('contract.holding', 'holding');
        $query->innerJoin('contract.property', 'property');
        $query->innerJoin('property.propertyMapping', 'mapping');
        $query->innerJoin('ord.transactions', 'transaction');

        $query->where("transaction.depositDate = :depositDate");
        $query->andWhere('transaction.isSuccessful = 1 and transaction.status = :reversedStatus');
        $query->andWhere('mapping.externalPropertyId IS NOT NULL');
        $query->andWhere('ord.status in (:orderStatuses)');
        $query->andWhere('operation.type = :rentStatus OR operation.type = :otherStatus');
        $query->andWhere('mapping.holding = :holdingId');

        $query->setParameter('depositDate', $depositDate->format('Y-m-d'));
        $query->setParameter('orderStatuses', [OrderStatus::REFUNDED, OrderStatus::RETURNED]);
        $query->setParameter('rentStatus', OperationType::RENT);
        $query->setParameter('otherStatus', OperationType::OTHER);
        $query->setParameter('reversedStatus', TransactionStatus::REVERSED);
        $query->setParameter('holdingId', $holding->getId());
        $query->setFirstResult($start);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param  User $user
     * @param  array $excludedStatuses
     * @return Order[]
     */
    public function getUserOrders(User $user, array $excludedStatuses = [OrderStatus::NEWONE])
    {
        $query = $this->createQueryBuilder('ord');
        $query->where('ord.user = :user');
        $query->setParameter('user', $user);

        if (!empty($excludedStatuses)) {
            $query->andWhere('ord.status not in (:orderStatuses)');
            $query->setParameter('orderStatuses', $excludedStatuses);
        }

        return $query->getQuery()->execute();
    }
}
