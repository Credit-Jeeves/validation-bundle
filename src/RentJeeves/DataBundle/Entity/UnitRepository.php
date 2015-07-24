<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class UnitRepository extends EntityRepository
{
    /**
     * @param Property $property
     * @param Group $group
     *
     * @return array
     */
    public function getUnitsArray(Property $property,Group $group = null)
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
     * @return array<Unit>
     */
    public function getUnitsByAddress($params)
    {
        $number = isset($params['number']) ? $params['number'] : '';
        $street = isset($params['street']) ? $params['street'] : '';
        $state = isset($params['state']) ? $params['state'] : '';
        $city = isset($params['city']) ? $params['city'] : '';
        $zip = isset($params['zip']) ? $params['zip'] : '';

        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.property', 'p')
            ->innerJoin('p.property_groups', 'g')
            ->where('p.number = :number')
            ->andWhere('p.street = :street')
            ->andWhere('p.area = :state')
            ->andWhere('p.city = :city')
            ->andWhere('p.zip = :zip')
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
            ->innerJoin('u.contractsWaiting', 'cw')
            ->where('cw.id IN (:ids)')
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
}
