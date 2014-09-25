<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class HoldingRepository extends EntityRepository
{

    public function findHoldingsForUpdatingBalance()
    {
        $query = $this->createQueryBuilder('h');
        $query->innerJoin('h.yardiSettings', 'ys');
        $query->where('ys.syncBalance = 1');
        $query = $query->getQuery();

        return $query->execute();
    }

    public function findHoldingsWithYardiSettings($start, $limit)
    {
        $query = $this->createQueryBuilder('holding');
        $query->innerJoin('holding.yardiSettings', 'yardiSetting');
        $query->setFirstResult($start);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }
}
