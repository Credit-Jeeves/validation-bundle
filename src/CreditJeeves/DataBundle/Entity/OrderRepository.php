<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ExternalApi;
use Doctrine\ORM\Query\Expr;
use DateTime;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;

class OrderRepository extends EntityRepository
{
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
        $query->innerJoin('prop.propertyAddress', 'propertyAddress');
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
            $query->andWhere('o.paymentType != :cash');
            $query->setParameter('cash', OrderPaymentType::CASH);
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
        $searchBy = 'propertyAddress.street',
        $search = '',
        $showCashPayments = true
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('prop.propertyAddress', 'propertyAddress');
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
            case 'date-initiated':
                $query->orderBy('o.created_at', $order);
                break;
            case 'property':
                $query->orderBy('propertyAddress.number', $order);
                $query->addOrderBy('propertyAddress.street', $order);
                $query->addOrderBy('unit.name', $order);
                break;
            default:
                $sort = 'o.' . $sort;
                $query->orderBy($sort, $order);
                break;
        }
        $this->applySortField($sort);
        if (!$showCashPayments) {
            $query->andWhere('o.paymentType != :cash');
            $query->setParameter('cash', OrderPaymentType::CASH);
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
                $field = 'CONCAT(propertyAddress.street, propertyAddress.number)';
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
                $field = 'propertyAddress.street';
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
     * @return OrderSubmerchant[]
     */
    public function getOrdersForYardiGenesis(
        $start,
        $end,
        array $groups,
        $exportBy,
        Property $property = null
    ) {
        return $this->getOrdersForRealPageReport($groups, $start, $end, $exportBy, $property);
    }

    /**
     * @param array $groups
     * @param string $start
     * @param string $end
     * @param string $exportBy
     * @param Property $property
     * @return mixed
     */
    public function getOrdersForRealPageReport(
        array $groups,
        $start,
        $end,
        $exportBy,
        Property $property = null
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
            $query->andWhere("transaction.status = 'complete'");
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
        $query->innerJoin('o.transactions', 'transaction');
        $query->innerJoin('t.group', 'g');
        $query->innerJoin('g.groupSettings', 'gs');

        if ($exportBy === ExportReport::EXPORT_BY_DEPOSITS) {
            $query->where('o.status IN (:statuses)');
            $query->andWhere('transaction.isSuccessful = 1 AND transaction.depositDate IS NOT NULL');
            $query->andWhere("transaction.status = 'complete'");
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

        $query->andWhere('o.paymentType in (:paymentType)');
        $query->andWhere('g.id in (:groups)');
        $query->andWhere('gs.isIntegrated = 1');
        $query->andWhere('res.holding = :holding');
        $query->setParameter('end', $end);
        $query->setParameter('start', $start);

        $groupsId = [];
        foreach ($groups as $group) {
            $groupsId[] = $group->getId();
        }
        $query->setParameter('paymentType', [OrderPaymentType::CARD, OrderPaymentType::BANK]);
        $query->setParameter('groups', $groups);
        $query->setParameter('holding', $group->getHolding());
        $query->orderBy('res.residentId', 'ASC');
        $query->orderBy('transaction.batchId', 'ASC');
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param Group $group
     * @param string $filter
     * @param string $search
     * @param string $batchId
     * @param string $depositDate
     * @return Order[]
     */
    public function getDepositedOrders(Group $group, $filter, $search, $batchId, $depositDate)
    {
        $ordersQuery = $this->createQueryBuilder('o')
            ->innerJoin('o.operations', 'p')
            ->innerJoin('p.contract', 't')
            ->innerJoin('o.transactions', 'h')
            ->where('t.group = :group')
            ->andWhere('h.depositDate IS NOT NULL')
            ->setParameter('group', $group);
        if ($batchId) {
            $ordersQuery
                ->andWhere('h.batchId = :batchId')
                ->setParameter('batchId', $batchId);
        } else {
            $ordersQuery
                ->andWhere('h.batchId is null')
                ->andWhere('h.depositDate = :depositDate')
                ->setParameter('depositDate', $depositDate);
        }

        if (!empty($filter) && !empty($search)) {
            $ordersQuery
                ->andWhere('h.' . $filter . ' = :search')
                ->setParameter('search', $search);
        }

        return $ordersQuery->getQuery()->execute();
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
        $query->innerJoin('property.propertyMappings', 'mapping');
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
        $query->innerJoin('property.propertyMappings', 'mapping');
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
     * @return OrderSubmerchant[]
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

    /**
     * @return Order[]
     */
    public function findOrdersForChurnRecapture()
    {
        $currentDate = new \DateTime();

        $forMinus2month = clone $currentDate;
        $minus2month = $forMinus2month->modify('-2 month');

        $forMinus1month = clone $currentDate;
        $minus1month = $forMinus1month->modify('-1 month');

        $subQuery = '
          SELECT o2
          FROM DataBundle:Order o2
          WHERE o2.cj_applicant_id = o.cj_applicant_id
          AND o2.status IN (:statuses)
          AND o2.paymentType in (:paymentTypes)
          AND o2.fee IS NOT NULL
          AND o2.sum > 3
          AND o2.created_at >= :minus1month AND o2.created_at < :currentDate
        ';

        $subQueryActivePayment = '
          SELECT p FROM RjDataBundle:Payment p WHERE p.status=\'active\' and p.contract = c.id
        ';

        return $this->createQueryBuilder('o')
            ->innerJoin('o.operations', 'op')
            ->innerJoin('op.contract', 'c')
            ->where('o.status in (:statuses)')
            ->where('o.sum > 3')
            ->andWhere('o.paymentType in (:paymentTypes)')
            ->andWhere('o.fee IS NOT NULL')
            ->andWhere('o.created_at >= :minus2month AND o.created_at < :minus1month')
            ->andWhere('c.finishAt > :currentDate')
            ->andWhere('c.status in (:contractStatuses)')
            ->andWhere(sprintf('NOT EXISTS (%s)', $subQuery))
            ->andWhere(sprintf('NOT EXISTS (%s)', $subQueryActivePayment))
            ->setParameter('statuses', [OrderStatus::COMPLETE, OrderStatus::PENDING])
            ->setParameter('paymentTypes', [OrderPaymentType::BANK, OrderPaymentType::CARD])
            ->setParameter('contractStatuses', [ContractStatus::APPROVED, ContractStatus::CURRENT])
            ->setParameter('currentDate', $currentDate)
            ->setParameter('minus2month', $minus2month)
            ->setParameter('minus1month', $minus1month)
            ->groupBy('o.cj_applicant_id')// need 1 order for 1 tenant
            ->orderBy('o.created_at', 'DESC')// need Order with max created_at
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $groups
     * @param string $start
     * @param string $end
     * @param string $exportBy
     * @return Order[]
     */
    public function getOrdersForBostonPostReport(array $groups, $start, $end, $exportBy)
    {
        if (empty($groups)) {
            throw new \LogicException('Must have at least one group');
        }

        $groupsId = [];
        foreach ($groups as $group) {
            $groupsId[] = $group->getId();
        }

        $query = $this->createQueryBuilder('o')
            ->innerJoin('o.operations', 'p')
            ->innerJoin('p.contract', 't')
            ->innerJoin('o.transactions', 'transaction')
            ->innerJoin('t.group', 'g')
            ->innerJoin('g.groupSettings', 'gs');

        if ($exportBy === ExportReport::EXPORT_BY_DEPOSITS) {
            $query->where('o.status IN (:statuses)')
                ->andWhere('transaction.isSuccessful = 1 AND transaction.depositDate IS NOT NULL')
                ->andWhere('transaction.status = :complete')
                ->andWhere('transaction.depositDate BETWEEN :start AND :end')
                ->setParameter('statuses', [OrderStatus::COMPLETE])
                ->setParameter('complete', TransactionStatus::COMPLETE);
        } else {
            $query->where('o.status IN (:statuses)')
                ->andWhere('STR_TO_DATE(o.created_at, \'%Y-%c-%e\') BETWEEN :start AND :end')
                ->setParameter('statuses', [
                    OrderStatus::COMPLETE,
                    OrderStatus::PENDING
                ]);
        }

        $query->andWhere('o.paymentType in (:paymentType)')
            ->andWhere('g.id in (:groups)')
            ->andWhere('gs.isIntegrated = 1')
            ->andWhere('t.holding = :holding')
            ->setParameter('end', $end)
            ->setParameter('start', $start)
            ->setParameter('paymentType', [OrderPaymentType::CARD, OrderPaymentType::BANK])
            ->setParameter('groups', $groups)
            ->setParameter('holding', $group->getHolding())
            ->orderBy('transaction.batchId', 'ASC');

        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param Contract $contract
     * @return int
     */
    public function countOrdersByContract(Contract $contract)
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(DISTINCT o.id)')
            ->innerJoin('o.operations', 'op')
            ->where('op.contract = :contract')
            ->setParameter('contract', $contract)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
