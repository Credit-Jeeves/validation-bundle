<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;

class ContractRepository extends EntityRepository
{
    public function countContracts($group, $searchBy = 'address', $search = '')
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group = :group');
        $query->andWhere('c.paid_to > :date OR c.paid_to IS NULL OR c.status = :status');
        $query->setParameter('group', $group);
        $query->setParameter('date', new \Datetime());
        $query->setParameter('status', ContractStatus::FINISHED);
        if (!empty($search)) {
            //             $query->andWhere('p.'.$searchBy.' = :search');
            //             $query->setParameter('search', $search);
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }
    
    public function getContractsPage(
        $group,
        $page = 1,
        $limit = 100,
        $sort = 'c.status',
        $order = 'ASC',
        $searchBy = 'p.street',
        $search = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group = :group');
        $query->andWhere('c.paid_to > :date OR c.paid_to IS NULL  OR c.status = :status');
        $query->setParameter('group', $group);
        $query->setParameter('date', new \Datetime());
        $query->setParameter('status', ContractStatus::FINISHED);
        if (!empty($search)) {
            //             $query->andWhere('p.'.$searchBy.' = :search');
            //             $query->setParameter('search', $search);
        }
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
            //             $query->andWhere('p.'.$searchBy.' = :search');
            //             $query->setParameter('search', $search);
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
        if (!empty($search)) {
            //             $query->andWhere('p.'.$searchBy.' = :search');
            //             $query->setParameter('search', $search);
        }
        $query->orderBy($sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }
}
