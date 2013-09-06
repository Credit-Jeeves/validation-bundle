<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class LandlordRepository extends EntityRepository
{

    public function getLandlordsByGroup($groupId)
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.agent_groups', 'ga');
        $query->where('ga.id = :group');
        $query->setParameter('group', $groupId);
        $query = $query->getQuery();

        return $query->execute();
    }
}
