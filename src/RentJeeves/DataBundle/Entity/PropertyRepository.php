<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function countProperties($group)
    {
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    public function getPropetiesPage($group, $page = 1, $limit = 2)
    {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('p');
        $query->innerJoin('p.property_groups', 'g');
        $query->where('g.id = :group_id');
        $query->setParameter('group_id', $group->getId());
        $query->setFirstResult( $offset );
        $query->setMaxResults( $limit );
        $query = $query->getQuery();
        return $query->execute();
    }
}
