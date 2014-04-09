<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\Query;
use DateTime;
use Doctrine\ORM\Query\Expr;

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
     *
     * @param Query $query
     * @param string $searchField
     * @param string $searchString
     *
     * @return mixed
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
     * @param Query $query
     * @param string $sortField
     * @param string $sortOrder
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
                    $query->orderBy('t.last_name', $sortOrder);
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
     * @param Group $group
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
     * @param integer $page
     * @param integer $limit
     * @param string $sortField
     * @param string $sortOrder
     * @param string $searchField
     * @param string $searchString
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
     * @param Group $group
     * @param string $searchField
     * @param string $searchString
     *
     * @return mixed
     */
    public function countActionsRequired($group, $searchField = 'address', $searchString = '')
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where(
            'c.group = :group AND c.status <> :status1 AND c.status <> :status2'.
            ' AND (c.paidTo < :date OR c.finishAt < :today)'
        );
        $query->setParameter('group', $group);
        $query->setParameter('date', new DateTime());
        $query->setParameter('today', new DateTime());
        $query->setParameter('status1', ContractStatus::FINISHED);
        $query->setParameter('status2', ContractStatus::DELETED);

        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    /**
     * @param Group $group
     * @param int $page
     * @param int $limit
     * @param string $sortField
     * @param string $sortOrder
     * @param string $searchField
     * @param string $searchString
     *
     * @return mixed
     */
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
        $query->where(
            'c.group = :group AND c.status <> :status1 AND c.status <> :status2'.
            ' AND (c.paidTo < :date OR c.finishAt < :today)'
        );
        $query->setParameter('group', $group);
        $query->setParameter('date', new DateTime());
        $query->setParameter('today', new DateTime());
        $query->setParameter('status1', ContractStatus::FINISHED);
        $query->setParameter('status2', ContractStatus::DELETED);
        $query = $this->applySearchFilter($query, $searchField, $searchString);
        $query = $this->applySortOrder($query, $sortField, $sortOrder);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
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
        $query->andWhere('c.status = :status');
        $query->setParameter('tenant', $tenant->getId());
        $query->setParameter('status', ContractStatus::CURRENT);
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

    public function getPaymentsToLandlord($status = array(OrderStatus::COMPLETE))
    {
        $start = new DateTime();
        $end = new DateTime('+1 day');
        $query = $this->createQueryBuilder('c');
        $query->select('SUM(o.sum) AS amount, h.id, g.id as group_id');
        $query->innerJoin('c.holding', 'h');
        $query->innerJoin('c.group', 'g');
        $query->innerJoin('c.operations', 'operation');
        $query->innerJoin('operation.order', 'o');
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

    public function getLateContracts($days = 5)
    {
        $query = $this->createQueryBuilder('c');
        $query->leftJoin('c.operations', 'op');
        $query->leftJoin('op.order', 'o', Expr\Join::WITH, 'o.status = :orderStatus');
        $query->setParameter('orderStatus', OrderStatus::PENDING);
        $query->andWhere('o.id IS NULL');
        $query->andWhere('c.paidTo BETWEEN :start AND :now');
        $query->setParameter('start', new DateTime('-'.$days.' days'));
        $query->setParameter('now', new DateTime());
        $query->andWhere('c.status = :status');
        $query->setParameter('status', ContractStatus::APPROVED);
        $query = $query->getQuery();
        return $query->execute();
    }

    public function getAllLateContracts($holding, $status = array(ContractStatus::CURRENT, ContractStatus::APPROVED))
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.holding = :holding');
        $query->andWhere('c.status IN (:status)');
        $query->andWhere('c.paidTo < :date');
        $query->setParameter('holding', $holding);
        $query->setParameter('status', $status);
        $query->setParameter('date', new DateTime());
        $query = $query->getQuery();
        return $query->iterate();
    }

    public function getImportContract($tenant, $unitName)
    {
        $query = $this->createQueryBuilder('contract');
        $query->leftJoin('contract.unit', 'unit');
        $query->leftJoin('contract.tenant', 'tenant');
        $query->where('contract.status = :approved OR contract.status = :current');
        $query->andWhere('tenant.id = :tenantId');
        $query->andWhere('unit.name = :unitName');
        // if 2 or more contract get contract with status current in first priority
        $query->addOrderBy('contract.status', "DESC");
        //If 2 or more contract, get last updated
        $query->addOrderBy('contract.updatedAt', "DESC");
        $query->setParameter('approved', ContractStatus::APPROVED);
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('tenantId', $tenant);
        $query->setParameter('unitName', $unitName);
        $query->setMaxResults(1);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getContractInviteForImport($tenant, $unitName)
    {
        $query = $this->createQueryBuilder('contract');
        $query->innerJoin('contract.unit', 'unit');
        $query->innerJoin('contract.tenant', 'tenant');
        $query->where('contract.status = :invite');
        $query->andWhere('tenant.id = :tenantId');
        $query->andWhere('unit.name = :unitName');
        // if 2 or more contract get contract with status current in first priority
        $query->addOrderBy('contract.status', "DESC");
        //If 2 or more contract, get last updated
        $query->addOrderBy('contract.updatedAt', "DESC");
        $query->setParameter('invite', ContractStatus::INVITE);
        $query->setParameter('tenantId', $tenant);
        $query->setParameter('unitName', $unitName);
        $query->setMaxResults(1);
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

    public function getContractsForExperianRentalReport($monthNo, $yearNo)
    {
        $firstDate = new DateTime("$yearNo-$monthNo-01");
        $lastDate = new DateTime($firstDate->format('Y-m-t'));

        $query = $this->createQueryBuilder('c');
        $query->where('c.reportToExperian = 1 AND c.experianStartAt is not NULL AND c.experianStartAt <= :startDate');
        $query->andWhere('c.status = :current OR c.status = :finished and c.finishAt BETWEEN :startDate AND :lastDate');
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('finished', ContractStatus::FINISHED);
        $query->setParameter('startDate', $firstDate);
        $query->setParameter('lastDate', $lastDate);
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getContractsForTransUnionRentalReport($monthNo, $yearNo)
    {
        $firstDate = new DateTime("$yearNo-$monthNo-01");
        $lastDate = new DateTime($firstDate->format('Y-m-t'));

        $query = $this->createQueryBuilder('c');
        $query->where(
            'c.reportToTransUnion = 1 AND c.transUnionStartAt is not NULL AND c.transUnionStartAt <= :startDate'
        );
        $query->andWhere('c.status = :current OR c.status = :finished and c.finishAt BETWEEN :startDate AND :lastDate');
        $query->setParameter('current', ContractStatus::CURRENT);
        $query->setParameter('finished', ContractStatus::FINISHED);
        $query->setParameter('startDate', $firstDate);
        $query->setParameter('lastDate', $lastDate);
        $query = $query->getQuery();

        return $query->execute();
    }
}
