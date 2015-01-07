<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;

class UnitMappingRepository extends EntityRepository
{
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
}
