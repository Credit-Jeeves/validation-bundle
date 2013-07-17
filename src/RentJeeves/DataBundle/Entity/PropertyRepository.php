<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function countProperties($group, $searchBy = 'address', $search = '')
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $query->andWhere('p.'.$searchBy.' = :search');
            $query->setParameter('search', $search);
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    public function getPropetiesPage($group, $page = 1, $limit = 100, $sort = 'number', $order = 'ASC', $searchBy = 'address', $search = '')
    {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $query->andWhere('p.'.$searchBy.' = :search');
            $query->setParameter('search', $search);
        }
        $query->orderBy('p.'.$sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }
}
