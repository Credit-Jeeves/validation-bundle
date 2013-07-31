<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function countProperties($group, $searchBy = 'street', $search = '')
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $query->andWhere('p.'.$searchBy.' LIKE :search');
            $query->setParameter('search', '%'.$search.'%');
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    public function getPropetiesPage(
        $group,
        $page = 1,
        $limit = 100,
        $sort = 'number',
        $isSortAsc = true,
        $searchBy = 'street',
        $search = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        if (!empty($search)) {
            $query->andWhere('p.'.$searchBy.' LIKE :search');
            $query->setParameter('search', '%'.$search.'%');
        }

        if ($isSortAsc) {
            $order = 'ASC';
        } else {
            $order = 'DESC';
        }
        $query->orderBy('p.'.$sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    public function countGroup($propertyId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('count(g.id)')
            ->leftJoin('p.property_groups', 'g')
            ->where('g.id IS NOT NULL')
            ->andWhere('p.id = :propertyId')
            ->setParameter('propertyId', $propertyId);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function landlordHasProperty($landlord)
    {
        $query = $this->createQueryBuilder('c');
        $query->select('count(c.id)');
        $query->leftJoin('c.property_groups', 'g');
        $query->leftJoin('g.holding', 'h');
        $query->leftJoin('h.users', 'u');
        $query->where('u.id = :landlord');
        $query->setParameter('landlord', $landlord->getId());

        $query = $query->getQuery();
        $count = $query->getSingleScalarResult();
        
        if ($count > 0) {
            return true;
        }

        return false;
    }
}
