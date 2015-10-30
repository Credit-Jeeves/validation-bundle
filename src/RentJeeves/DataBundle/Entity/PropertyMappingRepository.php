<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;

class PropertyMappingRepository extends EntityRepository
{
    /**
     * @param int $holdingId
     * @param int $page
     * @param int $limit
     * @return PropertyMapping[]
     */
    public function findUniqueByHolding($holdingId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;

        $query = $this->getUniqueByHoldingQuery($holdingId);
        $query->groupBy('pm.externalPropertyId');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->getQuery()->execute();
    }

    /**
     * @param int $holdingId
     * @return int
     */
    public function getCountUniqueByHolding($holdingId)
    {
        $query = $this->getUniqueByHoldingQuery($holdingId);
        $query->select('count(distinct pm.externalPropertyId)');

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $holdingId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getUniqueByHoldingQuery($holdingId)
    {
        $query = $this->createQueryBuilder('pm');
        $query->innerJoin('pm.property', 'p');
        $query->innerJoin('p.contracts', 'c');

        $query->where('c.status in (:statuses)');
        $query->andWhere('pm.holding = :holdingId');

        $query->setParameter('statuses', [ContractStatus::INVITE, ContractStatus::APPROVED, ContractStatus::CURRENT]);
        $query->setParameter('holdingId', $holdingId);

        return $query;
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
}
