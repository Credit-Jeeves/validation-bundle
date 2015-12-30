<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class UnitMappingRepository extends EntityRepository
{
    /**
     * @param Holding $holding
     * @param string $externalUnitId
     * @return UnitMapping
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUnitMappingByHoldingAndExternalUnitId(Holding $holding, $externalUnitId)
    {
        return $this->createQueryBuilder('mapping')
            ->innerJoin('mapping.unit', 'u')
            ->where('u.holding = :holdingId')
            ->andWhere('mapping.externalUnitId = :externalUnitId')
            ->setParameter('holdingId', $holding->getId())
            ->setParameter('externalUnitId', $externalUnitId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getMappingForImport(Group $group, $externalUnitId)
    {
        return $this->createQueryBuilder('mapping')
            ->innerJoin(
                'mapping.unit',
                'u'
            )
            ->innerJoin(
                'u.group',
                'g'
            )
            ->where('g.id = :groupId')
            ->andWhere('mapping.externalUnitId = :externalUnitId')
            ->setParameter('groupId', $group->getId())
            ->setParameter('externalUnitId', $externalUnitId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getMappingScopedByGroup(Property $property, Group $group, $externalUnitId)
    {
        return $this->createQueryBuilder('mapping')
            ->innerJoin(
                'mapping.unit',
                'u'
            )
            ->innerJoin(
                'u.group',
                'g'
            )
            ->innerJoin(
                'u.property',
                'p'
            )
            ->where('g.id = :groupId')
            ->andWhere('p.id = :propertyId')
            ->andWhere('mapping.externalUnitId = :externalUnitId')
            ->setParameter('groupId', $group->getId())
            ->setParameter('externalUnitId', $externalUnitId)
            ->setParameter('propertyId', $property->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Property $property
     * @param string $externalPropertyId
     * @return UnitMapping|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUnitMappingByPropertyAndExternalUnitId(Property $property, $externalPropertyId)
    {
        return $this->createQueryBuilder('mapping')
            ->innerJoin('mapping.unit', 'u')
            ->where('u.property = :property')
            ->andWhere('mapping.externalUnitId = :externalUnitId')
            ->setParameter('property', $property)
            ->setParameter('externalUnitId', $externalPropertyId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
