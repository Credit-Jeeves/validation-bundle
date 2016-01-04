<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;

class PropertyMappingRepository extends EntityRepository
{
    /**
     * @param Holding $holding
     * @return array
     */
    public function findUniqueExternalPropertyIdsByHolding(Holding $holding)
    {
        return $this->createQueryBuilder('pm')
            ->select('pm.externalPropertyId')
            ->innerJoin('pm.property', 'p')
            ->innerJoin('p.contracts', 'c')
            ->where('pm.holding = :holding')
            ->andWhere('c.status IN (:statuses)')
            ->setParameter('holding', $holding)
            ->setParameter('statuses', [ContractStatus::INVITE, ContractStatus::APPROVED, ContractStatus::CURRENT])
            ->groupBy('pm.externalPropertyId')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param Holding $holding
     * @return PropertyMapping[]
     */
    public function getByHoldingAndGroupByExternalPropertyID(Holding $holding)
    {
        $query = $this->createQueryBuilder('pm');
        $query->groupBy('pm.externalPropertyId');
        $query->andWhere('pm.holding = :holdingId');

        $query->setParameter('holdingId', $holding->getId());

        return $query->getQuery()->execute();
    }

    /**
     * @param  Property $property
     * @param  string $externalPropertyId
     * @param  string $accountingSystem
     * @return PropertyMapping
     * @throws NonUniqueResultException
     */
    public function getPropertyMappingByPropertyAndExternalPropertyBelongAccountingSystem(
        Property $property,
        $externalPropertyId,
        $accountingSystem
    ) {
        ApiIntegrationType::throwsInvalid($accountingSystem);

        return $this->createQueryBuilder('pm')
            ->innerJoin('pm.holding', 'h')
            ->andWhere('pm.property = :property')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('h.apiIntegrationType = :apiIntegrationType')
            ->setParameter('property', $property)
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter('apiIntegrationType', $accountingSystem)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param  Property $property
     * @param  Unit $unit
     * @param  string $externalPropertyId
     * @param  string $accountingSystem
     * @return PropertyMapping
     * @throws NonUniqueResultException
     */
    public function getPropertyMappingByPropertyUnitAndExternalPropertyBelongAccountingSystem(
        Property $property,
        Unit $unit,
        $externalPropertyId,
        $accountingSystem
    ) {
        ApiIntegrationType::throwsInvalid($accountingSystem);

        return $this->createQueryBuilder('pm')
            ->innerJoin('pm.property', 'p')
            ->innerJoin('pm.holding', 'h')
            ->innerJoin('p.units', 'units')
            ->andWhere('pm.property = :property')
            ->andWhere('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('units.id = :unit')
            ->andWhere('h.apiIntegrationType = :apiIntegrationType')
            ->setParameter('property', $property)
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter('unit', $unit)
            ->setParameter('apiIntegrationType', $accountingSystem)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Group $group
     * @return array
     */
    public function getPropertiesMappingByGroup(Group $group)
    {
        return $this->createQueryBuilder('pm')
            ->select('pm.externalPropertyId')
            ->innerJoin('pm.property', 'p')
            ->innerJoin('p.property_groups', 'c')
            ->where('c.id = :group')
            ->setParameter('group', $group)
            ->groupBy('pm.externalPropertyId')
            ->getQuery()
            ->getArrayResult();
    }
}
