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

    public function clearResidentContracts($residentId, $groupId)
    {
        $query = $this->createQueryBuilder('c');
        $query->delete();
        $query->where('c.residentId = :residentId');
        $query->andWhere('c.group = :groupId');
        $query->setParameter('groupId', $groupId);
        $query->setParameter('residentId', $residentId);
        $query->getQuery()->execute();
    }

    /**
     * @param Holding $holding
     * @param string  $residentId
     *
     * @return ContractWaiting[]
     */
    public function findAllByHoldingAndResidentId(Holding $holding, $residentId)
    {
        return $this->createQueryBuilder('cw')
            ->innerJoin('cw.group', 'groups')
            ->where('groups.holding = :holding')
            ->andWhere('cw.residentId = :residentId')
            ->setParameter('holding', $holding)
            ->setParameter('residentId', $residentId)
            ->getQuery()
            ->execute();
    }
}
