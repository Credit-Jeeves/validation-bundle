<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;

class GroupRepository extends EntityRepository
{
    public function getGroupsWithoutDepositAccount(Holding $holding)
    {
        $query = $this->createQueryBuilder('g');
        $query->leftJoin('g.depositAccount', 'da');
        $query->where("g.holding = :holdingId");
        $query->andWhere("da.id IS NULL");
        $query->setParameter('holdingId', $holding->getId());
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getGroupsWithPendingContracts(Holding $holding)
    {
        $query = $this->createQueryBuilder('g');
        $query->select('g.name as group_name, count(g.id) as amount_pending');
        $query->innerJoin('g.contracts', 'c');
        $query->where("g.holding = :holdingId");
        $query->andWhere("c.status = :statusPending");
        $query->groupBy('g.id');
        $query->setParameter('holdingId', $holding->getId());
        $query->setParameter('statusPending', ContractStatus::PENDING);
        $query = $query->getQuery();

        return $query->execute();
    }

    public function getCountPendingContracts(Group $group)
    {
        $query = $this->createQueryBuilder('g');
        $query->select('count(g.id) as amount_pending');
        $query->innerJoin('g.contracts', 'c');
        $query->where("g.id = :groupId");
        $query->andWhere("c.status = :statusPending");
        $query->setParameter('groupId', $group->getId());
        $query->setParameter('statusPending', ContractStatus::PENDING);
        $query = $query->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param $accountNumber
     * @param  Holding                                $holding
     * @return null|Group
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getGroupByAccountNumber($accountNumber, Holding $holding)
    {
        return $this->createQueryBuilder('g')
            ->join('g.accountNumberMapping', 'd')
            ->where('d.accountNumber = :accountNumber')
            ->andWhere('d.holding = :holding')
            ->setParameter('accountNumber', $accountNumber)
            ->setParameter('holding', $holding)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Holding $holding
     *
     * @return Group[]
     */
    public function getAllGroupIdsInHolding(Holding $holding)
    {
        return $this->createQueryBuilder('g')
            ->select('g.id')
            ->where('g.holding = :holding')
            ->setParameter('holding', $holding)
            ->getQuery()
            ->getResult();
    }
}
