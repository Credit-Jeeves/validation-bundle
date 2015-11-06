<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class ContractWaitingRepository extends EntityRepository
{
    /**
     * @deprecated
     *
     * @see ContractWaitingRepository::findOneByPropertyMappingExternalUnitIdResident()
     *
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

    /**
     * @param PropertyMapping $propertyMapping
     * @param string $externalUnitId
     * @param string $residentId
     *
     * @return ContractWaiting
     */
    public function findOneByPropertyMappingExternalUnitIdAndResidentId(
        PropertyMapping $propertyMapping,
        $externalUnitId,
        $residentId
    ) {
        return $this->createQueryBuilder('contract')
            ->innerJoin('contract.unit', 'u')
            ->innerJoin('u.unitMapping', 'um')
            ->innerJoin('contract.property', 'p')
            ->innerJoin('contract.group', 'g')
            ->innerJoin('p.propertyMapping', 'pm', Expr\Join::WITH, 'g.holding = pm.holding')
            ->innerJoin('g.groupSettings', 'gs')
            ->where('contract.residentId = :residentId')
            ->andWhere('pm.externalPropertyId = :propertyId')
            ->andWhere('gs.isIntegrated = 1')
            ->andWhere('um.externalUnitId = :externalUnitId')
            ->setParameter('propertyId', $propertyMapping->getExternalPropertyId())
            ->setParameter('externalUnitId', $externalUnitId)
            ->setParameter('residentId', $residentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Holding $holding
     * @param Property $property
     * @param string $unitName
     * @param string $residentId
     * @return ContractWaiting|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * @param Holding $holding
     * @param Property $property
     * @param string $residentId
     * @return ContractWaiting[]
     */
    public function findByHoldingPropertyResident(Holding $holding, Property $property, $residentId)
    {
        $query = $this->createQueryBuilder('contract');
        $query->select('contract')
            ->innerJoin('contract.group', 'g')
            ->innerJoin('g.groupSettings', 'gs')
            ->where('contract.residentId = :residentId')
            ->andWhere('contract.property = :propertyId')
            ->andWhere('g.holding = :holdingId')
            ->andWhere('gs.isIntegrated = 1')
            ->setParameter('propertyId', $property->getId())
            ->setParameter('holdingId', $holding->getId())
            ->setParameter('residentId', $residentId);

        $query = $query->getQuery();

        return $query->execute();
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

    /**
     * @param Holding $holding
     * @param Property $property
     * @param string $unitName
     * @param string $externalLeaseId
     * @return ContractWaiting[]|null
     */
    public function findByHoldingPropertyUnitExternalLeaseId(
        Holding $holding,
        Property $property,
        $unitName,
        $externalLeaseId
    ) {
        $query = $this->createQueryBuilder('contract')
            ->select('contract')
            ->innerJoin('contract.unit', 'u')
            ->innerJoin('contract.group', 'g')
            ->innerJoin('g.groupSettings', 'gs')
            ->where('contract.externalLeaseId = :externalLeaseId')
            ->andWhere('contract.property = :propertyId')
            ->andWhere('g.holding = :holdingId')
            ->andWhere('gs.isIntegrated = 1')
            ->andWhere('u.name = :unitName')
            ->setParameter('propertyId', $property->getId())
            ->setParameter('holdingId', $holding->getId())
            ->setParameter('unitName', $unitName)
            ->setParameter('externalLeaseId', $externalLeaseId);

        return $query->getQuery()->execute();
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @param string $externalResidentId
     * @param string $externalUnitId
     * @return ContractWaiting[]
     */
    public function findContractsByHoldingExternalPropertyResidentExternalUnitId(
        Holding $holding,
        $externalPropertyId,
        $externalResidentId,
        $externalUnitId
    ) {
        return $this->createQueryBuilder('cw')
            ->innerJoin('cw.unit', 'u')
            ->innerJoin('u.unitMapping', 'um')
            ->innerJoin('cw.group', 'g')
            ->innerJoin('g.groupSettings', 'gs')
            ->innerJoin('cw.property', 'p')
            ->innerJoin('p.propertyMapping', 'pm')
            ->where('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('pm.holding = :holding')
            ->andWhere('g.holding = :holding')
            ->andWhere('gs.isIntegrated = 1')
            ->andWhere('um.externalUnitId = :externalUnitId')
            ->andWhere('cw.residentId = :residentId')
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter('holding', $holding)
            ->setParameter('externalUnitId', $externalUnitId)
            ->setParameter('residentId', $externalResidentId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @param string $externalResidentId
     * @param string $unitName
     * @return ContractWaiting[]
     */
    public function findContractsByHoldingExternalPropertyResidentUnit(
        Holding $holding,
        $externalPropertyId,
        $externalResidentId,
        $unitName
    ) {
        return $this->createQueryBuilder('cw')
            ->innerJoin('cw.unit', 'u')
            ->innerJoin('cw.group', 'g')
            ->innerJoin('g.groupSettings', 'gs')
            ->innerJoin('cw.property', 'p')
            ->innerJoin('p.propertyMapping', 'pm')
            ->where('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('pm.holding = :holding')
            ->andWhere('g.holding = :holding')
            ->andWhere('gs.isIntegrated = 1')
            ->andWhere('(u.name = :unitName OR (u.name = :singleUnitName AND p.isSingle = 1))')
            ->andWhere('cw.residentId = :residentId')
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter('holding', $holding)
            ->setParameter('unitName', $unitName)
            ->setParameter('singleUnitName', UNIT::SINGLE_PROPERTY_UNIT_NAME)
            ->setParameter('residentId', $externalResidentId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Holding $holding
     * @param string $externalPropertyId
     * @param string $externalLeaseId
     * @return ContractWaiting[]
     */
    public function findContractsByHoldingExternalPropertyLease(
        Holding $holding,
        $externalPropertyId,
        $externalLeaseId
    ) {
        return $this->createQueryBuilder('cw')
            ->innerJoin('cw.unit', 'u')
            ->innerJoin('cw.group', 'g')
            ->innerJoin('g.groupSettings', 'gs')
            ->innerJoin('cw.property', 'p')
            ->innerJoin('p.propertyMapping', 'pm')
            ->where('pm.externalPropertyId = :externalPropertyId')
            ->andWhere('pm.holding = :holding')
            ->andWhere('g.holding = :holding')
            ->andWhere('gs.isIntegrated = 1')
            ->andWhere('cw.externalLeaseId = :externalLeaseId')
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter('holding', $holding)
            ->setParameter('externalLeaseId', $externalLeaseId)
            ->getQuery()
            ->execute();
    }
}
