<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use RentJeeves\DataBundle\Enum\ContractStatus;

class PropertyMappingRepository extends EntityRepository
{
    /**
     * @param Holding $holding
     * @return array
     */
    public function findUniqueExternalPropertyIdsByHolding(Holding $holding)
    {
        return $this->createQueryBuilder('pm')
            ->select('pm.externalPropertyId')
            ->innerJoin('pm.property', 'p')
            ->innerJoin('p.contracts', 'c')
            ->where('pm.holding = :holding')
            ->andWhere('c.status IN (:statuses)')
            ->setParameter('holding', $holding)
            ->setParameter('statuses', [ContractStatus::INVITE, ContractStatus::APPROVED, ContractStatus::CURRENT])
            ->groupBy('pm.externalPropertyId')
            ->getQuery()
            ->getArrayResult();
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
