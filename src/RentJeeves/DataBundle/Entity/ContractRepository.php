<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;

class ContractRepository extends EntityRepository
{
    public function countContracts($group, $searchBy = '', $search = '')
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search)) {
            $this->applyCollum($searchBy);
            $query->andWhere($searchBy.' LIKE :search');
            $query->setParameter('search', '%'.$search.'%');
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    private function applyCollum(&$field)
    {
        switch ($field) {
            case 'phone':
            case 'email':
                $field= 't.'.$field;
                break;
            case 'tenant':
                $field = 'CONCAT(t.first_name, t.last_name)';
                break;
            case 'first_name':
                $field = 't.first_name';
                break;
            case 'street':
                $field = 'p.street';
                break;
            default:
                $field = 'c.'.$field;
                break;
        }
    }

    public function getContractsPage(
        $group,
        $page = 1,
        $limit = 100,
        $sort = 'c.status',
        $order = 'ASC',
        $searchBy = '',
        $search = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search) && !empty($searchBy)) {
            $this->applyCollum($searchBy);
            $query->andWhere($searchBy.' LIKE :search');
            $query->setParameter('search', '%'.$search.'%');
        }
        $this->applyCollum($sort);
        $query->orderBy($sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    public function countActionsRequired($group, $searchBy = 'address', $search = '')
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
        if (!empty($search)) {
            $searchBy = $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy.' LIKE :search');
                $query->setParameter('search', '%'.$item.'%');
            }
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    public function getActionsRequiredPage(
        $group,
        $page = 1,
        $limit = 100,
        $sort = 'p.street',
        $order = 'ASC',
        $searchBy = 'p.street',
        $search = ''
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
        if (!empty($search) && !empty($searchBy)) {
            $searchBy = $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy.' LIKE :search');
                $query->setParameter('search', '%'.$item.'%');
            }
        }
        switch ($sort) {
            case 'statusA':
                $sort = 'c.status';
                break;
            case 'due_dateA':
                $sort = 'c.due_day';
                break;
            case 'propertyA':
                $sort = 'CONCAT(p.street, p.number)';
                break;
            case 'tenantA':
                $sort = 'CONCAT(t.first_name, t.last_name)';
                break;
            case 'amountA':
                $sort = 'c.rent';
                break;
            default:
                $sort = 'c.status';
                break;
        }
        $query->orderBy($sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    private function applySearchField($searchBy)
    {
        switch ($searchBy) {
            case 'property':
                $searchBy = 'CONCAT(p.street, p.number)';
                break;
            case 'tenant':
                $searchBy = 'CONCAT(t.first_name, t.last_name)';
                break;
            case 'amount':
                $searchBy = 'c.rent';
                break;
            case 'phone':
            case 'email':
                    $searchBy= 't.'.$searchBy;
                    break;
            default:
                $searchBy = 'c.'.$searchBy;
                break;
        }
        return $searchBy;
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
}
