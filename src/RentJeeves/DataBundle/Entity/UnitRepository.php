<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;

/**
 * @method Unit find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 */
class UnitRepository extends EntityRepository
{
    /**
     * @param Property $property
     * @param Group $group
     *
     * @return array
     */
    public function getUnitsArray(Property $property, Group $group = null)
    {
        $result = [];
        foreach ($this->getUnits($property, $group) as $unit) {
            $result[] = [
                'id' => $unit->getId(),
                'name' => $unit->getName(),
            ];
        }

        return $result;
    }

    /**
     * @param Property $property
     * @param Group $group
     *
     * @return Unit[]
     */
    public function getUnits(Property $property, Group $group = null)
    {

        $query = $this->createQueryBuilder('u');
        $query->select('LENGTH(u.name) as co,u');
        $query->where('u.property = :property');
        $query->setParameter('property', $property);
        if ($group) {
            $query->andWhere('u.group = :group');
            $query->setParameter('group', $group);
        }
        $query->addOrderBy('co', 'ASC');
        $query->addOrderBy('u.name', 'ASC');
        $query = $query->getQuery();

        $data = $query->execute();
        /**
         * Remove co and make simple array object
         */
        $result = array();
        if (empty($data)) {
            return $result;
        }

        foreach ($data as $unit) {
            $result[] = reset($unit);
        }

        return $result;
    }

    /**
     * @param Property $property
     * @param Group $group
     * @param string $unitName
     * @return null|Unit
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getImportUnitByPropertyGroupAndUnitName(Property $property, Group $group, $unitName)
    {
        return $this->createQueryBuilder('u')
            ->where('u.property = :property')
            ->andWhere('u.group = :group')
            ->andWhere('u.name = :unitName')
            ->setParameter('property', $property)
            ->setParameter('group', $group)
            ->setParameter('unitName', $unitName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $groupId
     * @param null $unitName
     * @param null $externalUnitId
     *
     * @return null|Unit
     */
    public function getImportUnit($groupId, $unitName = null, $externalUnitId = null)
    {
        if (empty($unitName) && empty($externalUnitId)) {
            return null;
        }

        $query = $this->createQueryBuilder('u');
        $query->leftJoin('u.group', 'g');
        $query->innerJoin('u.property', 'p');
        $query->where('g.id = :groupId');

        if (!empty($externalUnitId)) {
            $query->innerJoin('u.unitMapping', 'm');
            $query->andWhere('m.externalUnitId = :unitId');
            $query->setParameter('unitId', $externalUnitId);
        }

        if (!empty($unitName)) {
            $query->andWhere('u.name = :unitName');
            $query->setParameter('unitName', $unitName);
        }

        $query->setParameter('groupId', $groupId);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return Unit
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUnitWithLandlord($id)
    {
        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.property', 'p')
            ->innerJoin('p.property_groups', 'g')
            ->where('u.id = :unit')
            ->setParameter('unit', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return Units belongs to this Property with mapped Groups
     *
     * @param Property $property
     * @return array<Unit>
     */
    public function getUnitsByPropertyWithGroup(Property $property)
    {
        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.property', 'p')
            ->innerJoin('p.property_groups', 'g')
            ->where('u.property = :property')
            ->setParameter('property', $property)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $params
     *
     * @return array<Unit>
     */
    public function getUnitsByAddress(array $params)
    {
        $number = isset($params['number']) ? $params['number'] : '';
        $street = isset($params['street']) ? $params['street'] : '';
        $state = isset($params['state']) ? $params['state'] : '';
        $city = isset($params['city']) ? $params['city'] : '';
        $zip = isset($params['zip']) ? $params['zip'] : '';

        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.property', 'p')
            ->innerJoin('p.propertyAddress', 'propertyAddress')
            ->innerJoin('p.property_groups', 'g')
            ->where('propertyAddress.number = :number')
            ->andWhere('propertyAddress.street = :street')
            ->andWhere('propertyAddress.state = :state')
            ->andWhere('propertyAddress.city = :city')
            ->andWhere('propertyAddress.zip = :zip')
            ->setParameter('number', $number)
            ->setParameter('street', $street)
            ->setParameter('state', $state)
            ->setParameter('city', $city)
            ->setParameter('zip', $zip)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $contractWaitingIds
     *
     * @return Unit[]
     */
    public function findAllByContractWaitingIds(array $contractWaitingIds)
    {
        if (true === empty($contractWaitingIds)) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->addSelect('CONCAT(propertyAddress.number, propertyAddress.street) AS HIDDEN sortField')
            ->innerJoin('u.contractsWaiting', 'cw')
            ->innerJoin('u.property', 'property')
            ->innerJoin('property.propertyAddress', 'propertyAddress')
            ->where('cw.id IN (:ids)')
            ->orderBy('sortField')
            ->setParameter('ids', implode(' , ', $contractWaitingIds))
            ->getQuery()
            ->execute();
    }

    /**
     * @param Holding $holding
     * @param string $externalUnitId
     *
     * @return Unit|null
     */
    public function findOneByHoldingAndExternalId(Holding $holding, $externalUnitId)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.unitMapping', 'um')
            ->where('um.externalUnitId = :externalId')
            ->andWhere('u.holding = :holding')
            ->setParameter('holding', $holding)
            ->setParameter('externalId', $externalUnitId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Unit $currentUnit
     * @param Unit $excludedUnit
     *
     * @return Unit[]
     */
    public function findOtherUnitsWithSameExternalUnitIdInGroupExcludeUnit(Unit $currentUnit, Unit $excludedUnit)
    {
        if (null === $currentUnit->getUnitMapping()) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->innerJoin('u.unitMapping', 'um')
            ->where('um.externalUnitId = :externalId')
            ->andWhere('u.group = :group')
            ->andWhere('u.id != :excludedUnitId AND u.id != :currentUnitId')
            ->setParameter('group', $currentUnit->getGroup())
            ->setParameter('currentUnitId', $currentUnit->getId())
            ->setParameter('excludedUnitId', $excludedUnit->getId())
            ->setParameter('externalId', $currentUnit->getUnitMapping()->getExternalUnitId())
            ->getQuery()
            ->execute();
    }

    /**
     * @param Unit $currentUnit
     * @param Property $property
     *
     * @return Unit|null
     */
    public function findFirstUnitsWithSameNameByUnitAndPropertyAndSortById(Unit $currentUnit, Property $property)
    {
        return $this->createQueryBuilder('u')
            ->where('u.property = :property')
            ->andWhere('u.name = :unitName')
            ->andWhere('u.id != :excludedUnitId')
            ->setParameter('property', $property)
            ->setParameter('unitName', $currentUnit->getActualName())
            ->setParameter('excludedUnitId', $currentUnit->getId())
            ->setMaxResults(1)
            ->orderBy('u.deletedAt', 'asc')
            ->addOrderBy('u.id', 'asc')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
