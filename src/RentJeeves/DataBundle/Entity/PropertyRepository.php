<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PropertyRepository extends EntityRepository
{
    public function getPropertiesByGroup($group)
    {
        $query = $this->createQueryBuilder('p')
            ->where('i.cj_applicant_id = :nUserId')
            ->setParameter('nUserId', $group->getId())
            ->getQuery();
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
}
