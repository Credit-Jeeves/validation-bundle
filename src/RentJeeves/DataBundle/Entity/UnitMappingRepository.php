<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;

class UnitMappingRepository extends EntityRepository
{
    public function getMappingForImport(Group $group, $externalUnitId)
    {
        $this->createQueryBuilder('mapping')
            ->innerJoin(
                'mapping.unit',
                'unit'
            )
            ->innerJoin(
                'unit.group',
                'group'
            )
            ->where('group.id = :group')
            ->andWhere('mapping.externalUnitId = :externalUnitId')
            ->setParameter('group', $group->getId())
            ->setParameter('externalUnitId', $externalUnitId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
