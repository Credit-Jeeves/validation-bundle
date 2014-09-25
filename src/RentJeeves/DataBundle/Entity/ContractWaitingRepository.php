<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class ContractWaitingRepository extends EntityRepository
{
    public function findByHoldingPropertyUnitResident(Holding $holding, Property $property, $unitName, $residentId)
    {
        $query = $this->createQueryBuilder('contract');
        $query->select('contract');
        $query->innerJoin('contract.unit', 'u');
        $query->innerJoin('contract.group', 'g');
        $query->innerJoin('g.groupSettings', 'gs');

        $query->where('contract.residentId = :residentId');
        $query->andWhere('contract.property = :propertyId');
        $query->andWhere('g.holding = :holdingId');
        $query->andWhere('gs.isIntegrated = 1');
        $query->andWhere('u.name = :unitName');

        $query->setParameter('propertyId', $property->getId());
        $query->setParameter('holdingId', $holding->getId());
        $query->setParameter('unitName', $unitName);
        $query->setParameter('residentId', $residentId);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }
}
