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

    public function getLandlordByContract(Contract $contract)
    {
        $group = $contract->getGroup();
        $holding = $contract->getHolding();

        $query = $this->createQueryBuilder('landlord');
        $query->leftJoin('landlord.agent_groups', 'groupLandlord');
        $query->where('groupLandlord.id = :groupId');

        if (!empty($holding)) {
            $query->leftJoin('landlord.holding', 'holding');
            $query->orWhere('holding.id = :holdingId');
            $query->setParameter('holdingId', $holding->getId());
        }

        $query->orderBy('landlord.is_super_admin', 'DESC');
        $query->setParameter('groupId', $group->getId());

        $query->setMaxResults(1);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }
}
