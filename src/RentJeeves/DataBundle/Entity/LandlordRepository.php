<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;

class LandlordRepository extends EntityRepository
{

    /**
     * @param integer $groupId
     * @return Landlord[]
     */
    public function getLandlordsAdmin($groupId)
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.holding', 'h')
            ->innerJoin('h.groups', 'g')
            ->where('l.is_super_admin = 1 OR l.is_holding_admin=1')
            ->andWhere('g.id = :group')
            ->setParameter('group', $groupId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param integer $groupId
     * @return Landlord[]
     */
    public function getLandlordsByGroup($groupId)
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.agent_groups', 'ga')
            ->where('ga.id = :group')
            ->andWhere('l.is_super_admin = 0')
            ->andWhere('l.is_holding_admin = 0')
            ->setParameter('group', $groupId)
            ->getQuery()->execute();
    }

    public function getLandlordsByGroupNoAdmin($groupId)
    {
        $query = $this->createQueryBuilder('c');
        $query->innerJoin('c.agent_groups', 'ga');
        $query->where('ga.id = :group');
        $query->andWhere('c.is_holding_admin = 0 AND c.is_super_admin = 0');
        $query->setParameter('group', $groupId);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param integer $holdingId
     * @return Landlord[]
     */
    public function getHoldingAdmins($holdingId)
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.holding = :holdingId');
        $query->andWhere('c.is_holding_admin = 1 OR c.is_super_admin = 1');
        $query->setParameter('holdingId', $holdingId);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param integer $holdingId
     * @return Landlord[]
     */
    public function getHoldingNoneAdmins($holdingId)
    {
        $query = $this->createQueryBuilder('c');
        $query->where('c.holding = :holdingId');
        $query->andWhere('c.is_holding_admin = 0 and c.is_super_admin = 0');
        $query->setParameter('holdingId', $holdingId);
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getLandlordByContract(Contract $contract)
    {
        $group = $contract->getGroup();
        $holding = $contract->getHolding();

        $query = $this->createQueryBuilder('landlord');
        $query->leftJoin('landlord.agent_groups', 'groupLandlord');
        $query->leftJoin('landlord.holding', 'holding');

        $query->where('groupLandlord.id = :groupId OR holding.id = :holdingId');
        $query->orderBy('landlord.is_super_admin', 'DESC');

        $query->setParameter('groupId', $group->getId());
        $query->setParameter('holdingId', $holding->getId());

        $query->setMaxResults(1);
        $query = $query->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return Landlord[]
     */
    public function findNotPayDirectHoldingAdmins()
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.holding', 'h')
            ->innerJoin('h.groups', 'g')
            ->where('l.is_super_admin = 1')
            ->andWhere('g.orderAlgorithm != :orderAlgorithm')
            ->setParameter('orderAlgorithm', OrderAlgorithmType::PAYDIRECT)
            ->orderBy('l.email', 'DESC')
            ->getQuery()
            ->execute();
    }

    /**
     * @return Landlord[]
     */
    public function findNotPayDirectHoldingNotAdmins()
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.holding', 'h')
            ->innerJoin('h.groups', 'g')
            ->where('l.is_super_admin = 0')
            ->andWhere('g.orderAlgorithm != :orderAlgorithm')
            ->setParameter('orderAlgorithm', OrderAlgorithmType::PAYDIRECT)
            ->orderBy('l.email', 'DESC')
            ->getQuery()
            ->execute();
    }
}
