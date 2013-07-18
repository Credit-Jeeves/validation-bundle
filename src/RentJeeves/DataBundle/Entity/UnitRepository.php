<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UnitRepository extends EntityRepository
{
    public function getUnitsArray($property, $holding = null, $group = null)
    {
        $query = $this->createQueryBuilder('u');
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
        $query->orderBy('u.name');
        $query = $query->getQuery();
        $units = $query->execute();
        $result = array();
        foreach ($units as $unit) {
            $item = array();
            $item['id'] = $unit->getId();
            $item['name'] = $unit->getName();
            $result[] = $item;
        }
        return $result;
    }
}
