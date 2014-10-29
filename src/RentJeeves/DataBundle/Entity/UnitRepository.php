<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UnitRepository extends EntityRepository
{
    public function getUnitsArray($property, $holding = null, $group = null)
    {
        $units = $this->getUnits($property, $holding, $group);
        $result = array();
        foreach ($units as $unit) {
            $item = array();
            $item['id'] = $unit->getId();
            $item['name'] = $unit->getName();
            $result[] = $item;
        }
        return $result;
    }

    public function getUnits($property, $holding = null, $group = null)
    {

        $query = $this->createQueryBuilder('u');
        $query->select('LENGTH(u.name) as co,u');
        $query->where('u.property = :property');
        $query->setParameter('property', $property);
        if ($holding) {
            $query->andWhere('u.holding = :holding');
            $query->setParameter('holding', $holding);
        }
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
        $query->where('g.id = :groupId OR (p.isSingle=1 AND g.id IS NULL)');

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

    public function getUnitWithLandlord($id)
    {
        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.property', 'p')
            ->innerJoin('u.group', 'g')
            ->innerJoin('u.holding', 'h')
            ->where('u.id = :unit')
            ->setParameter('unit', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUnitsByAddress($params)
    {
        $number = $params['number'];
        $street = $params['street'];
        $state = $params['state'];
        $city = $params['city'];
        $zip = $params['zip'];

        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.property', 'p')
            ->innerJoin('u.group', 'g')
            ->innerJoin('u.holding', 'h')
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
}
