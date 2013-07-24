<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ContractRepository extends EntityRepository
{
    public function countContracts($group, $searchBy = 'address', $search = '')
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.property', 'p');
        $query->innerJoin('c.tenant', 't');
        $query->where('c.group = :group');
        $query->setParameter('group', $group);
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
        $query->setParameter('group', $group);
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
        $query->setParameter('group', $group);
        $query->setParameter('date', new \Datetime());
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
        $query->setParameter('group', $group);
        $query->setParameter('date', new \Datetime());
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
