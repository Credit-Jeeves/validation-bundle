<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\Query;

class ContractRepository extends EntityRepository
{
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
     */
    private function applySearchFilter($query, $searchField = '', $searchString = '')
    {
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
                case 'email':
                    $query->setParameter('t.'.$searchField, '%'.$searchString.'%');
                    break;
                case 'amount':
                case 'amountA':
                    $query->andWhere('c.rent LIKE :rent');
                    $query->setParameter('rent', '%'.$searchString.'%');
                    break;
                case 'status':
                case 'statusA':
                    $query->andWhere('c.status = :status');
                    $query->setparameter('status', $searchString);
                    break;
                default:
                    foreach ($search as $item) {
                        $query->andWhere('c'.$searchField.' LIKE :search');
                        $query->setParameter('search', '%'.$item.'%');
                    }
                    break;
            }
        }
        return $query;
    }

    /**
     * @param string $search
     * @return array
     */
    private function prepareSearch($search)
    {
        $search = preg_replace('/\s+/', ' ', trim($search));
        $search = explode(' ', $search);
        return $search;
    }

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
                    $query->orderBy('t.last_name', $sortOrder);
                    break;
                case 'statusA':
                    $query->orderBy('c.status', $sortOrder);
                    break;
                case 'due_dateA':
                    $query->orderBy('c.paidTo', $sortOrder);
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
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @param string $searchBy
     * @param string $search
     */
    public function countContracts(\CreditJeeves\DataBundle\Entity\Group $group, $searchField = '', $searchString = '')
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
     * @param integer $page
     * @param integer $limit
     * @param string $sortField
     * @param string $sortOrder
     * @param string $searchField
     * @param string $searchString
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
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $this->applySortOrder($query, $sortField, $sortOrder);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    public function countActionsRequired($group, $searchField = 'address', $searchString = '')
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group = :group');
        $query->andWhere('c.paidTo < :date');
        $query->andWhere('c.status <> :status');
        $query->setParameter('group', $group);
        $query->setParameter('date', new \Datetime());
        $query->setParameter('status', ContractStatus::FINISHED);
        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    public function getActionsRequiredPage(
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
        $query->where('c.group = :group');
        $query->andWhere('c.paidTo < :date');
        $query->andWhere('c.status <> :status');
        $query->setParameter('group', $group);
        $query->setParameter('date', new \Datetime());
        $query->setParameter('status', ContractStatus::FINISHED);
        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $this->applySortOrder($query, $sortField, $sortOrder);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    public function getCountByStatus($tenant, $status = null)
    {
        $query = $this->createQueryBuilder('c');
        $query->select('count(c.id)');
        $query->innerJoin('c.tenant', 't');
        $query->where('t.id = :tenant');
        if (!is_null($status)) {
            $query->andWhere('c.status =:status');
            $query->setParameter('status', $status);
        }
        $query->setParameter('tenant', $tenant->getId());
        $query = $query->getQuery();
        return $query->getSingleScalarResult();
    }

    public function countReporting($tenant)
    {
        $query = $this->createQueryBuilder('c');
        $query->select('count(c.id)');
        $query->innerJoin('c.tenant', 't');
        $query->where('t.id = :tenant');
        $query->andWhere('c.reporting = 1');
        $query->andWhere('c.status = :status');
        $query->setParameter('tenant', $tenant->getId());
        $query->setParameter('status', ContractStatus::CURRENT);
        $query = $query->getQuery();
        return $query->getSingleScalarResult();
    }

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

    public function getPaymentsToLandlord($status = array(OrderStatus::COMPLETE))
    {
        $start = new \Datetime();
        $end = new \Datetime('+1 day');
        $query = $this->createQueryBuilder('c');
        $query->select('SUM(o.amount) AS amount, h.id');
        $query->innerJoin('c.holding', 'h');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('c.operation', 'operation');
        $query->innerJoin('operation.orders', 'o');
        $query->where('o.status IN (:status)');
        $query->andWhere('o.updated_at BETWEEN :start AND :end');
        $query->groupBy('h.id');
        $query->setParameter('status', $status);
        $query->setParameter('start', $start->format('Y-m-d'));
        $query->setParameter('end', $end->format('Y-m-d'));
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
        $start = new \Datetime();
        $end = new \Datetime('+1 day');
        $query = $this->createQueryBuilder('c');
        $query->select('SUM(o.amount)');
        $query->innerJoin('c.holding', 'h');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('c.operation', 'operation');
        $query->innerJoin('operation.orders', 'o');
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
        $query->setParameter('date', new \Datetime());
        $query = $query->getQuery();
        $result = $query->getOneOrNullResult();
        return !empty($result) ? $result[1] : 0;
    }

    public function getLateContracts($days = 5)
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.paidTo BETWEEN :start AND :now');
        $query->setParameter('start', new \Datetime('-'.$days.' days'));
        $query->setParameter('now', new \Datetime());
        $query = $query->getQuery();
        return $query->iterate();
    }

    public function getAllLateContracts($holding, $status = array(ContractStatus::CURRENT, ContractStatus::APPROVED))
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.holding = :holding');
        $query->andWhere('c.status IN (:status)');
        $query->andWhere('c.paidTo < :date');
        $query->setParameter('holding', $holding);
        $query->setParameter('status', $status);
        $query->setParameter('date', new \Datetime());
        $query = $query->getQuery();
        return $query->iterate();
    }
}
