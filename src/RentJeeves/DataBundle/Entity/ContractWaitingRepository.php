<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class ContractWaitingRepository extends EntityRepository
{
    /**
     * @param Holding $holding
     * @param PropertyMapping $propertyMapping
     * @param string $externalUnitId
     * @param string $residentId
     * @return ContractWaiting
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByHoldingPropertyMappingExternalUnitIdResident(
        Holding $holding,
        PropertyMapping $propertyMapping,
        $externalUnitId,
        $residentId
    ) {
        $query = $this->createQueryBuilder('contract');
        $query->select('contract');
        $query->innerJoin('contract.unit', 'u');
        $query->innerJoin('u.unitMapping', 'um');
        $query->innerJoin('contract.property', 'p');
        $query->innerJoin('p.propertyMapping', 'pm');
        $query->innerJoin('contract.group', 'g');
        $query->innerJoin('g.groupSettings', 'gs');

        $query->where('contract.residentId = :residentId');
        $query->andWhere('pm.externalPropertyId = :propertyId');
        $query->andWhere('g.holding = :holdingId');
        $query->andWhere('gs.isIntegrated = 1');
        $query->andWhere('um.externalUnitId = :externalUnitId');

        $query->setParameter('propertyId', $propertyMapping->getExternalPropertyId());
        $query->setParameter('holdingId', $holding->getId());
        $query->setParameter('externalUnitId', $externalUnitId);
        $query->setParameter('residentId', $residentId);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

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
     * @param bool $sortReverse
     *
     * @return ContractWaiting[]
     */
    public function findAllByHoldingAndResidentId(Holding $holding, $residentId, $sortReverse = false)
    {
        $sort = $sortReverse ? 'DESC' : 'ASC';

        return $this->createQueryBuilder('cw')
            ->innerJoin('cw.group', 'groups')
            ->where('groups.holding = :holding')
            ->andWhere('cw.residentId = :residentId')
            ->setParameter('holding', $holding)
            ->setParameter('residentId', $residentId)
            ->orderBy('cw.id', $sort)
            ->getQuery()
            ->execute();
    }
}
