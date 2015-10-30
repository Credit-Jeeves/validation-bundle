<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use RentJeeves\DataBundle\Enum\ContractStatus;

class PropertyMappingRepository extends EntityRepository
{
    /**
     * Iterate can't work with join
     * @see http://www.doctrine-project.org/jira/browse/DDC-176
     * @param Holding $holding
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findUniqueByHolding(Holding $holding)
    {
        $query = $this->createQueryBuilder('pm');
        $query->select('pm');
        $query->add('from', new Expr\From('RjDataBundle:Property', 'p'), true);
        $query->add('from', new Expr\From('RjDataBundle:Contract', 'c'), true);
        $query->where('pm.holding = :holding');
        $query->andWhere('p.id = pm.property');
        $query->andWhere('p.id = c.property');
        $query->andWhere('c.status IN (:statuses)');
        $query->setParameter('statuses', [ContractStatus::INVITE, ContractStatus::APPROVED, ContractStatus::CURRENT]);
        $query->setParameter('holding', $holding);
        $query->groupBy('pm.externalPropertyId');

        return $query->getQuery()->iterate();
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
