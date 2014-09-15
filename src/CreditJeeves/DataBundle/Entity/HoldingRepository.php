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
}
