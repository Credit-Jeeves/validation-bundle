<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\Query;
use RentJeeves\CoreBundle\DateTime;
use Doctrine\ORM\Query\Expr;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\DataBundle\Enum\PaymentStatus;

class ContractRepository extends EntityRepository
{
    use DateCommon;
    /**
     * We'll use following aliases in this class
     * c - Contracts
     * t - Tenants
     * p - Properties
     * u - Units
     * h - Holdings
     * g - Group
     * d - deposit account
     * o - Orders
     *
     * In other cases, please use native names
     *
     * @param QueryBuilder $query
     * @param string       $searchField
     * @param string       $searchString
     *
     * @return mixed
     */
    private function applySearchFilter($query, $searchField = '', $searchString = '')
    {
        $isUseStatus = false;
        if (!empty($searchField) && !empty($searchString)) {
            $search = $this->prepareSearch($searchString);
            switch ($searchField) {
                case 'street':
                case 'address':
                case 'property':
                    foreach ($search as $item) {
                        $query->andWhere('CONCAT(p.number, p.street) LIKE :search');
                        $query->setParameter('search', '%'.$item.'%');
                    }
                    break;
                case 'tenant':
                case 'tenantA':
                    foreach ($search as $item) {
                        $query->andWhere('CONCAT(t.first_name, t.last_name) LIKE :search');
                        $query->setParameter('search', '%'.$item.'%');
                    }
                    break;
                case 'phone':
                    //Remove all chars except number
                    $searchString = preg_replace('[\D]', '', $searchString);
                    $query->andWhere('t.phone LIKE :search');
                    $query->setParameter('search', '%'.$searchString.'%');
                    break;
                case 'email':
                    $query->andWhere('t.email LIKE :search');
                    $query->setParameter('search', '%'.$searchString.'%');
                    break;
                case 'amount':
                case 'amountA':
                    $query->andWhere('c.rent LIKE :rent');
                    $query->setParameter('rent', '%'.$searchString.'%');
                    break;
                case 'status':
                case 'statusA':
                    $isUseStatus = true;
                    $query->andWhere('c.status = :status');
                    $query->setparameter('status', $searchString);
                    break;
                default:
                    foreach ($search as $item) {
                        $query->andWhere('c.'.$searchField.' LIKE :search');
                        $query->setParameter('search', '%'.$item.'%');
                    }
                    break;
            }
        }
        if (!$isUseStatus) {
            $query->andWhere('c.status <> :status');
            $query->setParameter('status', ContractStatus::DELETED);
        }

        return $query;
    }

    /**
     * @param string $search
     *
     * @return array
     */
    private function prepareSearch($search)
    {
        $search = preg_replace('/\s+/', ' ', trim($search));
        $search = explode(' ', $search);

        return $search;
    }

    /**
     * @param QueryBuilder $query
     * @param string       $sortField
     * @param string       $sortOrder
     *
     * @return mixed
     */
    private function applySortOrder($query, $sortField = '', $sortOrder = 'ASC')
    {
        if (!empty($sortField)) {
            switch ($sortField) {
                case 'street':
                case 'propertyA':
                case 'address':
                case 'property':
                    $query->orderBy('p.number', $sortOrder);
                    $query->addOrderBy('p.street', $sortOrder);
                    break;
                case 'amountA':
                case 'amount':
                    $query->orderBy('c.rent', $sortOrder);
                    break;
                case 'tenantA':
                case 'tenant':
                case 'first_name':
                    $query->orderBy('t.first_name', $sortOrder);
                    $query->addOrderBy('t.last_name', $sortOrder);
                    break;
                case 'statusA':
                    $query->orderBy('c.status', $sortOrder);
                    break;
                case 'email':
                    $query->orderBy('t.email', $sortOrder);
                    break;
                case 'due_dateA':
                    $query->orderBy('c.paidTo', $sortOrder);
                    break;
                case 'status':
                    $query->select(
                        "FIELD(c.status, 'pending', 'approved', 'current', 'invite', 'finished', 'deleted')
                            as HIDDEN status_sort_order,
                         c"
                    );
                    $query->orderBy('status_sort_order');
                    break;
                default:
                    $sortField = 'c.'.$sortField;
                    $query->orderBy($sortField, $sortOrder);
                    break;
            }
        }

        return $query;
    }

    /**
     * Count records for Tenant Tab
     *
     * @param Group  $group
     * @param string $searchBy
     * @param string $search
     *
     * @return mixed
     */
    public function countContracts(Group $group, $searchField = '', $searchString = '')
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.tenant', 't');
        $query->innerJoin('c.property', 'p');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $query->getQuery();

        return $query->getScalarResult();
    }

    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @param integer                               $page
     * @param integer                               $limit
     * @param string                                $sortField
     * @param string                                $sortOrder
     * @param string                                $searchField
     * @param string                                $searchString
     *
     * @return mixed
     */
    public function getContractsPage(
        $group,
        $page = 1,
        $limit = 100,
        $sortField = 'c.status',
        $sortOrder = 'ASC',
        $searchField = '',
        $searchString = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->leftJoin('t.settings', 's');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $this->applySortOrder($query, $sortField, $sortOrder);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param Group  $group
     * @param int    $page
     * @param int    $limit
     * @param string $sortField
     * @param string $sortOrder
     * @param string $searchField
     * @param string $searchString
     *
     * @return QueryBuilder
     */
    public function getActionsRequiredPageQuery(
        $group,
        $page = 1,
        $limit = 100,
        $sortField = 'p.street',
        $sortOrder = 'ASC',
        $searchField = 'p.street',
        $searchString = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where(
            '(c.group = :group AND c.status <> :status1 AND c.status <> :status2'.
            ' AND (c.paidTo < :date OR c.finishAt < :today ))' .
            ' AND c.id IN (SELECT IDENTITY(o.contract) FROM DataBundle:Operation o WHERE' .
            ' o.contract = c.id )'
        );
        $query->andWhere(
            'c.id NOT IN (
                SELECT con.id FROM RentJeeves\DataBundle\Entity\Contract con
                INNER JOIN con.operations op
                INNER JOIN op.order ord
                WHERE ord.status = :pending AND MONTH(op.paidFor) = :month
                AND YEAR(op.paidFor) = :year and op.type = :rent
            )'
        );

        if ($group->getGroupSettings()->getIsIntegrated()) {
            $query->andWhere('c.reportToExperian = 1 OR c.reportToTransUnion = 1 OR c.finishAt < :today ');
        }

        $today = new DateTime();
        $query->setParameter('group', $group);
        $query->setParameter('date', $today->format('Y-m-d'));
        $query->setParameter('today', $today->format('Y-m-d'));
        $query->setParameter('status1', ContractStatus::FINISHED);
        $query->setParameter('status2', ContractStatus::DELETED);

        $query->setParameter('month', $today->format('n'));
        $query->setParameter('year', $today->format('Y'));
        $query->setParameter('pending', OrderStatus::PENDING);
        $query->setParameter('rent', OperationType::RENT);

        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $this->applySortOrder($query, $sortField, $sortOrder);

        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query;
    }

    /**
     * @param Tenant $tenant
     *
     * @return mixed
     */
    public function countReporting($tenant)
    {
        $query = $this->createQueryBuilder('c');
        $query->select('count(c.id)');
        $query->innerJoin('c.tenant', 't');
        $query->where('t.id = :tenant');
        $query->andWhere(
            'c.reportToTransUnion = 1 OR c.reportToExperian = 1
            OR c.experianStartAt is not NULL OR c.transUnionStartAt is not NULL'
        );
        $query->setParameter('tenant', $tenant->getId());
        $query = $query->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * If all tenant contracts belong to groups with reportingIsOff
     * @param Tenant $tenant
     */
    public function countContractsWithReportingIsOff(Tenant $tenant)
    {
        $query = $this->createQueryBuilder('contract');

        $query->select('count(contract.id)');
        $query->innerJoin('contract.group', 'group');
        $query->innerJoin('group.groupSettings', 'groupSettings');

        $query->where('contract.tenant = :tenant');
        $query->andWhere('contract.status = :status');
        $query->andWhere('groupSettings.isReportingOff = 1');

        $query->setParameter('tenant', $tenant->getId());
        $query->setParameter('status', ContractStatus::CURRENT);
        $query = $query->getQuery();

        return $query->getSingleScalarResult();
    }

    public function countTenantContractsByStatus(Tenant $tenant, $status = ContractStatus::CURRENT)
    {
        $query = $this->createQueryBuilder('c');
        $query->select('count(c.id)');
        $query->innerJoin('c.tenant', 't');
        $query->where('t.id = :tenant');
        $query->andWhere('c.status = :status');
        $query->setParameter('tenant', $tenant->getId());
        $query->setParameter('status', $status);
        $query = $query->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param Landlord $landlord
     *
     * @return mixed|null
     */
    public function getContractsLandlord($landlord)
    {
        $groups = $landlord->getGroups();
        $groupArray = array();
        foreach ($groups as $value) {
            $groupArray[$value->getId()] = $value->getId();
        }
        if (empty($groupArray)) {
            return null;
        }
        $groupsIds = implode("','", $groupArray);
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group IN (:groups)');
        $query->setParameter('groups', $groupsIds);
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getPaymentsToLandlord(
        $orderStatus = array(OrderStatus::COMPLETE),
        $orderType = array(OrderPaymentType::BANK, OrderPaymentType::CARD)
    ) {
        $start = new DateTime();
        $end = new DateTime('+1 day');
        $query = $this->createQueryBuilder('c');
        $query->select('SUM(operation.amount) AS amount, h.id, g.id as group_id');
        $query->innerJoin('c.holding', 'h');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('c.operations', 'operation');
        $query->innerJoin('operation.order', 'o');
        $query->where('o.status IN (:orderStatus)');
        $query->setParameter('orderStatus', $orderStatus);
        $query->andWhere('o.paymentType in (:paymentType)');
        $query->setParameter('paymentType', $orderType);
        $query->andWhere('o.updated_at BETWEEN :start AND :end');
        $query->setParameter('start', $start->format('Y-m-d'));
        $query->setParameter('end', $end->format('Y-m-d'));
        $query->groupBy('h.id');
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getRentHoldings()
    {
        $query = $this->createQueryBuilder('c');
        $query->select('h, c');
        $query->innerJoin('c.holding', 'h');
        $query->groupBy('h.id');
        $query = $query->getQuery();

        return $query->iterate();
    }

    public function getPaymentsByStatus($holding, $status = OrderStatus::COMPLETE)
    {
        $start = new DateTime();
        $end = new DateTime('+1 day');
        $query = $this->createQueryBuilder('c');
        $query->select('SUM(o.sum)');
        $query->innerJoin('c.holding', 'h');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('c.operations', 'operation');
        $query->innerJoin('operation.order', 'o');
        $query->where('c.holding = :holding');
        $query->andWhere('o.status =:status');
        $query->andWhere('o.updated_at BETWEEN :start AND :end');
        $query->setParameter('holding', $holding);
        $query->setParameter('status', $status);
        $query->setParameter('start', $start->format('Y-m-d'));
        $query->setParameter('end', $end->format('Y-m-d'));
        $query = $query->getQuery();
        $result = $query->getOneOrNullResult();

        return !empty($result) ? $result[1] : 0;
    }

    public function getLateAmount($holding, $status = array(ContractStatus::CURRENT, ContractStatus::APPROVED))
    {
        $query = $this->createQueryBuilder('c');
        $query->select('SUM(c.rent)');
        $query->where('c.holding = :holding');
        $query->andWhere('c.status IN (:status)');
        $query->andWhere('c.paidTo < :date');
        $query->setParameter('holding', $holding);
        $query->setParameter('status', $status);
        $query->setParameter('date', new DateTime());
        $query = $query->getQuery();
        $result = $query->getOneOrNullResult();

        return !empty($result) ? $result[1] : 0;
    }

    /**
     * Complicated query, have unit test
     *
     * @param  int             $days
     * @return ArrayCollection
     */
    public function getLateContracts($days = 5)
    {
        $days *= -1;
        $date = new DateTime($days.' days');
        $now = new DateTime();
        $dueDays = $this->getDueDays(0, $date);

        $query = $this->createQueryBuilder('c');

        $query->leftJoin(
            'c.operations',
            'op',
            Expr\Join::WITH,
            "op.paidFor >= :paidForInterval"
        );
        $query->setParameter('paidForInterval', $now->getClone()->modify('-1 month'));

        $query->leftJoin(
            'op.order',
            'o',
            Expr\Join::WITH,
            'o.status IN (:orderStatuses)'
        );
        $query->setParameter('orderStatuses', array(OrderStatus::COMPLETE, OrderStatus::PENDING));

        $query->where('c.status IN (:contractStatuses)');
        $query->setParameter('contractStatuses', array(ContractStatus::APPROVED, ContractStatus::CURRENT));

        $query->andWhere('c.dueDate IN (:dueDays)');
        $query->setParameter('dueDays', $dueDays);

        $query->andWhere('o.id IS NULL');

        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param  Holding         $holding
     * @param  array           $status
     * @return ArrayCollection
     */
    public function getAllLateContractsByHolding(
        Holding $holding,
        $status = array(ContractStatus::CURRENT, ContractStatus::APPROVED)
    ) {
        $query = $this->createQueryBuilder('c');
        $query->leftJoin('c.operations', 'op');
        $query->leftJoin('op.order', 'o');
        $query->where('c.holding = :holding');
        $query->andWhere('c.status IN (:status)');
        $query->andWhere('c.paidTo < DATE(:date)');
        $query->andWhere('o.status <> :orderPendingStatus OR o.status IS NULL');
        $query->setParameter('holding', $holding);
        $query->setParameter('status', $status);
        $query->setParameter('orderPendingStatus', OrderStatus::PENDING);
        $query->setParameter('date', new DateTime());
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param  ArrayCollection $groups
     * @param  array           $status
     * @return ArrayCollection
     */
    public function getAllLateContractsByGroups($groups, $status = [ContractStatus::CURRENT, ContractStatus::APPROVED])
    {
        $query = $this->createQueryBuilder('c');
        $query->leftJoin('c.operations', 'op');
        $query->leftJoin('op.order', 'o');
        $query->where('c.group IN (:groups)');
        $query->andWhere('c.status IN (:status)');
        $query->andWhere('c.paidTo < DATE(:date)');
        $query->andWhere('o.status <> :orderPendingStatus OR o.status IS NULL');
        $query->setParameter('groups', $this->getGroupIds($groups));
        $query->setParameter('status', $status);
        $query->setParameter('orderPendingStatus', OrderStatus::PENDING);
        $query->setParameter('date', new DateTime());
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param  ArrayCollection $groups
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

    /**
     * @param  Tenant                                 $tenant
     * @param  Group                                  $group
     * @param  Holding                                $holding
     * @param  string                                 $externalLeaseId
     * @return null|Contract
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getImportContractByExtenalLeaseId(
        $externalLeaseId,
        Tenant $tenant,
        Group $group = null,
        Holding $holding = null
    ) {
        $query = $this->createQueryBuilder('contract');
        $query->innerJoin('contract.unit', 'unit');
        $query->innerJoin('contract.property', 'property');
        $query->where('contract.status = :approved OR contract.status = :current OR contract.status = :invite');
        $query->andWhere('contract.tenant = :tenantId');
        $query->andWhere('contract.externalLeaseId = :externalLeaseId');

        if ($holding) {
            $query->andWhere('contract.holding = :holding');
            $query->setParameter('holding', $holding);
        }

        if ($group) {
            $query->andWhere('contract.group = :group');
            $query->setParameter('group', $group);
        }

        $query->setParameter('externalLeaseId', $externalLeaseId);
        $query->setParameter('approved', ContractStatus::APPROVED);
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('invite', ContractStatus::INVITE);
        $query->setParameter('tenantId', $tenant->getId());

        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param  integer                                $tenantId
     * @param  string                                 $unitName
     * @param  string                                 $externalUnitId
     * @param  string                                 $propertyId
     * @param  Group                                  $group
     * @param  Holding                                $holding
     * @return Contract
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getImportContract(
        $tenantId,
        $unitName,
        $externalUnitId = null,
        $propertyId = null,
        Group $group = null,
        Holding $holding = null
    ) {
        $query = $this->createQueryBuilder('contract');
        $query->innerJoin('contract.unit', 'unit');
        $query->innerJoin('contract.property', 'property');
        $query->innerJoin('contract.tenant', 'tenant');
        $query->where('contract.status = :approved OR contract.status = :current OR contract.status = :invite');
        $query->andWhere('tenant.id = :tenantId');
        if (!empty($externalUnitId)) {
            $query->innerJoin('unit.unitMapping', 'uMap');
            $query->andWhere('uMap.externalUnitId = :externalUnit');
            $query->setParameter('externalUnit', $externalUnitId);
        } else {
            $query->andWhere('unit.name = :unitName');
            $query->setParameter('unitName', $unitName);
        }

        if (!is_null($propertyId)) {
            $query->andWhere('property.id = :propertyId');
            $query->setParameter('propertyId', $propertyId);
        }

        if ($holding) {
            $query->andWhere('contract.holding = :holding');
            $query->setParameter('holding', $holding);
        }

        if ($group) {
            $query->andWhere('contract.group = :group');
            $query->setParameter('group', $group);
        }

        $query->setParameter('approved', ContractStatus::APPROVED);
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('invite', ContractStatus::INVITE);
        $query->setParameter('tenantId', $tenantId);

        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getLastActivityDate()
    {
        $query = $this->createQueryBuilder('c');
        $query->select('c.updatedAt');
        $query->orderBy('c.updatedAt', 'DESC');
        $query->setMaxResults(1);
        $query = $query->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param \DateTime $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Contract[]
     */
    public function getContractsForExperianPositiveReport(\DateTime $month, \DateTime $startDate, \DateTime $endDate)
    {
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $query = $this->createQueryBuilder('c');
        $query->distinct();
        $query->innerJoin('c.operations', 'op', Expr\Join::WITH, 'op.type = :rent');
        $query->innerJoin('op.order', 'ord', Expr\Join::WITH, 'ord.status = :completeOrder');
        $this->whereReportToExperian($query, 'c', clone $startDate);
        $query->andWhere('c.status = :current');
        $query->andWhere('op.createdAt BETWEEN :startDate AND :endDate');
        $query->andWhere('MONTH(op.paidFor) = :month');
        $query->andWhere('YEAR(op.paidFor) = :year');

        $query->setParameter('rent', OperationType::RENT);
        $query->setParameter('completeOrder', OrderStatus::COMPLETE);
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $query->setParameter('month', $month->format('m'));
        $query->setParameter('year', $month->format('Y'));
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return QueryBuilder
     */
    protected function getBaseQueryForClosureReport(\DateTime $startDate, \DateTime $endDate)
    {
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $query = $this->createQueryBuilder('c');
        $query->where('c.status = :finished and c.finishAt BETWEEN :startDate AND :endDate');

        $query->setParameter('finished', ContractStatus::FINISHED);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);

        return $query;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Contract[]
     */
    public function getContractsForExperianClosureReport(\DateTime $startDate, \DateTime $endDate)
    {
        $query = $this->getBaseQueryForClosureReport($startDate, $endDate);
        $this->whereReportToExperian($query, 'c', clone $startDate);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param \DateTime $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return Contract[]
     */
    public function getContractsForExperianNegativeReport(\DateTime $month, \DateTime $startDate, \DateTime $endDate)
    {
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $subquery = $this->createQueryBuilder('c2');
        $subquery
            ->select('c2.id')
            ->distinct()
            ->innerJoin('c2.operations', 'op', Expr\Join::WITH, 'op.type = :rent')
            ->innerJoin('op.order', 'ord', Expr\Join::WITH, 'ord.status = :completeOrder')
            ->andWhere('c2.status = :current')
            ->andWhere('op.createdAt BETWEEN :startDate AND :endDate')
            ->andWhere('MONTH(op.paidFor) = :month')
            ->andWhere('YEAR(op.paidFor) = :year');
        $this->whereReportToExperian($subquery, 'c2', clone $startDate);

        $query = $this->createQueryBuilder('c');
        $query->distinct();
        $query->innerJoin('c.operations', 'operation', Expr\Join::WITH, 'operation.type = :rent');
        $query->innerJoin('operation.order', 'o', Expr\Join::WITH, 'o.status = :completeOrder');
        $this->whereReportToExperian($query, 'c', clone $startDate);
        $query->andWhere('c.status = :current');
        $query->andWhere(sprintf('c.id not in (%s)', $subquery->getDQL()));

        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $query->setParameter('rent', OperationType::RENT);
        $query->setParameter('completeOrder', OrderStatus::COMPLETE);
        $query->setParameter('month', $month->format('m'));
        $query->setParameter('year', $month->format('Y'));
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param \DateTime $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Contract[]
     */
    public function getContractsForTransUnionPositiveReport(\DateTime $month, \DateTime $startDate, \DateTime $endDate)
    {
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $query = $this->createQueryBuilder('c');
        $query->select(
            'c contract, sum(op.amount) total_amount, max(op.createdAt) last_payment_date, op.paidFor paid_for'
        );
        $query->innerJoin('c.operations', 'op', Expr\Join::WITH, 'op.type = :rent');
        $query->innerJoin('op.order', 'ord', Expr\Join::WITH, 'ord.status = :completeOrder');
        $this->whereReportToTransUnion($query, 'c', clone $startDate);
        $query->andWhere('c.status = :current');
        $query->andWhere('op.createdAt BETWEEN :startDate AND :endDate');
        $query->andWhere('MONTH(op.paidFor) = :month');
        $query->andWhere('YEAR(op.paidFor) = :year');
        $query->groupBy('c.id, op.paidFor');
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $query->setParameter('rent', OperationType::RENT);
        $query->setParameter('completeOrder', OrderStatus::COMPLETE);
        $query->setParameter('month', $month->format('m'));
        $query->setParameter('year', $month->format('Y'));
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param \DateTime $month
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Contract[]
     */
    public function getContractsForTransUnionNegativeReport(\DateTime $month, \DateTime $startDate, \DateTime $endDate)
    {
        $startDate->setTime(0, 0, 0);
        $endDate->setTime(23, 59, 59);

        $subquery = $this->createQueryBuilder('c2');
        $subquery
            ->select('c2.id')
            ->distinct()
            ->innerJoin('c2.operations', 'op', Expr\Join::WITH, 'op.type = :rent')
            ->innerJoin('op.order', 'ord', Expr\Join::WITH, 'ord.status = :completeOrder')
            ->andWhere('c2.status = :current')
            ->andWhere('op.createdAt BETWEEN :startDate AND :endDate')
            ->andWhere('MONTH(op.paidFor) = :month')
            ->andWhere('YEAR(op.paidFor) = :year');
        $this->whereReportToTransUnion($subquery, 'c2', clone $startDate);

        $query = $this->createQueryBuilder('c');
        $query->distinct();
        $query->innerJoin('c.operations', 'operation', Expr\Join::WITH, 'operation.type = :rent');
        $query->innerJoin('operation.order', 'o', Expr\Join::WITH, 'o.status = :completeOrder');
        $this->whereReportToTransUnion($query, 'c', clone $startDate);

        $query->andWhere('c.status = :current');
        $query->andWhere(sprintf('c.id not in (%s)', $subquery->getDQL()));

        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate);
        $query->setParameter('rent', OperationType::RENT);
        $query->setParameter('completeOrder', OrderStatus::COMPLETE);
        $query->setParameter('month', $month->format('m'));
        $query->setParameter('year', $month->format('Y'));
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Contract[]
     */
    public function getContractsForTransUnionClosureReport(\DateTime $startDate, \DateTime $endDate)
    {
        $query = $this->getBaseQueryForClosureReport($startDate, $endDate);
        $this->whereReportToTransUnion($query, 'c', clone $startDate);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param QueryBuilder $query
     * @param string $contractAlias
     * @param \DateTime $reportingStartDate
     * @return QueryBuilder
     */
    protected function whereReportToTransUnion(QueryBuilder $query, $contractAlias, \DateTime $reportingStartDate)
    {
        $reportingStartDate->setTime(23, 59, 59);

        $query->andWhere(sprintf(
            '%s.reportToTransUnion = 1 AND %s.transUnionStartAt is not NULL AND %s.transUnionStartAt <= :startDate',
            $contractAlias,
            $contractAlias,
            $contractAlias,
            $reportingStartDate
        ));
        $query->setParameter('startDate', $reportingStartDate);

        return $query;
    }

    /**
     * @param QueryBuilder $query
     * @param string $contractAlias
     * @param \DateTime $reportingStartDate
     * @return QueryBuilder
     */
    protected function whereReportToExperian(QueryBuilder $query, $contractAlias, \DateTime $reportingStartDate)
    {
        $reportingStartDate->setTime(23, 59, 59);

        $query->andWhere(sprintf(
            '%s.reportToExperian = 1 AND %s.experianStartAt is not NULL AND %s.experianStartAt <= :startDate',
            $contractAlias,
            $contractAlias,
            $contractAlias,
            $reportingStartDate
        ));
        $query->setParameter('startDate', $reportingStartDate);

        return $query;
    }

    /**
     * We have test for this query because query not so clear as I want
     * Test name ContractRepositoryCase
     *
     * @param  DateTime $date
     * @return mixed
     */
    public function getPotentialLateContract(DateTime $date)
    {
        $startPaymentDate = clone $date;

        $endPaymentDate = clone $date;
        $dueDays = $this->getDueDays(0, $date);

        $startPaymentDateDql = PaymentRepository::getStartDateDQLString('p');
        $dql = "
            c.dueDate IN(:dueDays)
            AND (
                c.status=:current
                OR
                c.status=:approved
            )
            AND (
                p.id IS NULL
                OR NOT (
                    {$startPaymentDateDql} <= STR_TO_DATE(:startDate,'%Y-%c-%e')
                    AND (
                        (p.endYear IS NULL AND p.endMonth IS NULL)
                        OR
                        (p.endYear > :endYear)
                        OR
                        (p.endYear = :endYear AND p.endMonth >= :endMonth)
                    )
                )
            )
        ";
        $query = $this->createQueryBuilder('c');
        $query->leftJoin(
            'c.payments',
            'p',
            Expr\Join::WITH,
            "p.status = :active"
        );

        $query->where($dql);
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('approved', ContractStatus::APPROVED);
        $query->setParameter('active', PaymentStatus::ACTIVE);
        $query->setParameter('startDate', $startPaymentDate->format('Y-m-d'));
        $query->setParameter('endMonth', $endPaymentDate->format('n'));
        $query->setParameter('endYear', $endPaymentDate->format('Y'));
        $query->setParameter('dueDays', $dueDays);

        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param QueryBuilder $query
     * @param string       $orderStatus
     * @param int          $monthAgo
     *
     * @return QueryBuilder
     */
    public static function queryOperationsOrdersHistory(&$query, $orderStatus = OrderStatus::COMPLETE, $monthAgo = 6)
    {
        $paidTo = new DateTime();
        $paidTo->modify("-{$monthAgo} months");
        $query->leftJoin(
            'c.operations',
            'op',
            Expr\Join::WITH,
            "op.paidFor > :paidTo"
        );
        $query->setParameter('paidTo', $paidTo->format('Y-m-d'));
        $query->leftJoin(
            'op.order',
            'o',
            Expr\Join::WITH,
            "o.status = :orderStatus"
        );
        $query->setParameter('orderStatus', $orderStatus);

        return $query;
    }

    /**
     * @param int $id
     *
     * @return Contract
     */
    public function findOneWithOperationsOrders($id)
    {
        $query = $this->createQueryBuilder('c');
        $query->andWhere('c.id = :id');
        $query->setParameter('id', $id);

        $query = static::queryOperationsOrdersHistory($query)->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findByTenantIdInvertedStatusesForPayments(
        $tenantId,
        $statuses = array(ContractStatus::DELETED)
    ) {
        $query = $this->createQueryBuilder('c');
        $query->leftJoin('c.holding', 'h');
        $query->leftJoin('c.property', 'p');
        $query->leftJoin('c.unit', 'u');
        $query->leftJoin('c.group', 'g');
        $query->leftJoin('g.depositAccount', 'da');
        $query->leftJoin('c.payments', 'pay');
        if (!empty($status)) {
            $query->andWhere('c.status NOT IN :statuses');
            $query->setParameter('statuses', $statuses);
        }
        $query->andWhere('c.tenant = :tenantId');
        $query->setParameter('tenantId', $tenantId);

        $query = static::queryOperationsOrdersHistory($query)->getQuery();

        return $query->execute();
    }

    public function getContractsForUpdateBalance($dueDays)
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.dueDate IN (:dueDays)');
        $query->andWhere('c.status = :status');
        $query->setParameter('status', ContractStatus::CURRENT);
        $query->setParameter('dueDays', $dueDays);
        $query = $query->getQuery();

        return $query->iterate();
    }

    /**
     * @param Holding $holding
     * @param Property $property
     * @param string $residentId
     * @param string $externalUnitId
     * @return Contract[]
     */
    public function findContractsByHoldingPropertyResidentAndExternalUnitId(
        Holding $holding,
        Property $property,
        $residentId,
        $externalUnitId
    ) {
        $query = $this->createQueryBuilder('c');
        $query->select('c');
        $query->innerJoin('c.unit', 'u');
        $query->innerJoin('u.unitMapping', 'um');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('g.groupSettings', 'gs');
        $query->innerJoin('c.tenant', 't');
        $query->innerJoin('t.residentsMapping', 'rm');

        $query->where('c.status in (:statuses)');
        $query->andWhere('c.property = :propertyId');
        $query->andWhere('c.holding = :holdingId');
        $query->andWhere('gs.isIntegrated = 1');
        $query->andWhere('um.externalUnitId = :externalUnitId');
        $query->andWhere('rm.residentId = :residentId');

        $query->setParameter('statuses', [ContractStatus::INVITE, ContractStatus::APPROVED, ContractStatus::CURRENT]);
        $query->setParameter('propertyId', $property->getId());
        $query->setParameter('holdingId', $holding->getId());
        $query->setParameter('externalUnitId', $externalUnitId);
        $query->setParameter('residentId', $residentId);
        $query = $query->getQuery();

        return $query->execute();
    }

    public function findContractByHoldingPropertyResidentUnit(
        Holding $holding,
        Property $property,
        $residentId,
        $unitName
    ) {
        $query = $this->createQueryBuilder('c');
        $query->select('c');
        $query->innerJoin('c.unit', 'u');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('g.groupSettings', 'gs');
        $query->innerJoin('c.tenant', 't');
        $query->innerJoin('t.residentsMapping', 'rm');

        $query->where('c.status in (:statuses)');
        $query->andWhere('c.property = :propertyId');
        $query->andWhere('c.holding = :holdingId');
        $query->andWhere('gs.isIntegrated = 1');
        $query->andWhere('u.name = :unitName');
        $query->andWhere('rm.residentId = :residentId');

        $query->setParameter('statuses', [ContractStatus::INVITE, ContractStatus::APPROVED, ContractStatus::CURRENT]);
        $query->setParameter('propertyId', $property->getId());
        $query->setParameter('holdingId', $holding->getId());
        $query->setParameter('unitName', $unitName);
        $query->setParameter('residentId', $residentId);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param  Tenant $tenant
     * @param  Unit   $unit
     * @return bool
     */
    public function isExistDuplicateByTenantUnit(Tenant $tenant, Unit $unit, $id = null)
    {
        return !!$this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.status NOT IN (:statuses)')
            ->andWhere('c.tenant = :tenant')
            ->andWhere('c.unit = :unit')
            ->andWhere('c.id <> :id')
            ->setParameters([
                'statuses' => [ContractStatus::FINISHED, ContractStatus::DELETED],
                'tenant' => $tenant,
                'unit' => $unit,
                'id' => (int) $id
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isExistDuplicateByTenantPropertyUnitname(Tenant $tenant, Property $property, $unitName, $id = null)
    {
        $query = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.status NOT IN (:statuses)')
            ->andWhere('c.tenant = :tenant')
            ->andWhere('c.property = :property')
            ->andWhere('c.id <> :id')
            ->setParameters([
                'statuses' => [ContractStatus::FINISHED, ContractStatus::DELETED],
                'tenant' => $tenant,
                'property' => $property,
                'id' => (int) $id
            ]);

        if ($unitName) {
            $query->andWhere('c.search = :search')->setParameter('search', $unitName);
        }

        return !!$query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param  Tenant $tenant
     * @return bool
     */
    public function isTurnedOnBureauReporting(Tenant $tenant)
    {
        return !!$this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.status IN (:statuses)')
            ->andWhere('c.tenant = :tenant')
            ->andWhere('c.reportToExperian = 1 OR c.reportToTransUnion = 1')
            ->setParameters([
                'statuses' => [ContractStatus::APPROVED, ContractStatus::CURRENT],
                'tenant' => $tenant
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $ids
     *
     * @return Contract[]
     */
    public function getContractsByIds(array $ids)
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.id in (:ids)');
        $query->setParameter('ids', $ids);
        $query = $query->getQuery();

        return $query->execute();
    }
}
