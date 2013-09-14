<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
//                         $query->andWhere('u.name LIKE :search');
//                         $query->setParameter('search', '%'.$item.'%');
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
                    //$query->innerJoin('c.unit', 'u');
                    //$query->addOrderBy('u.name', $sortOrder);
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
                    $query->orderBy('c.paid_to', $sortOrder);
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
        //$query->innerJoin('c.unit', 'u');
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
        //$query->innerJoin('c.unit', 'u');
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
        $query->andWhere('c.paid_to < :date');
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
        $query->andWhere('c.paid_to < :date');
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

    /**
     * 
     */
    public function getContractsForPayment()
    {
        $query = $this->createQueryBuilder('c');
        $query->add(
            'where',
            $query->expr()->in(
                'c.status',
                array(
                    ContractStatus::CURRENT,
                    ContractStatus::APPROVED
                )
            )
        );
        $query->andWhere('c.paid_to < :date');
        $query->setParameter('date', new \Datetime('+3 days'));
        $query = $query->getQuery();
        return $query->execute();//getResult(Query::HYDRATE_SIMPLEOBJECT);
    }
}
