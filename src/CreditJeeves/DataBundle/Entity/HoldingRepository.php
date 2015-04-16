<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

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
        $query->innerJoin('holding.accountingSettings', 'accountingSettings');
        $query->innerJoin('holding.yardiSettings', 'yardiSetting');
        $query->where('accountingSettings.apiIntegration = :yardi');
        $query->setParameter('yardi', ApiIntegrationType::YARDI_VOYAGER);
        $query->setFirstResult($start);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param string $apiIntegrationType
     * @return array
     */
    public function findAllByApiIntegration($apiIntegrationType)
    {
        $query = $this->createQueryBuilder('holding');
        $query->innerJoin('holding.accountingSettings', 'accountingSettings');
        $query->where('accountingSettings.apiIntegration = :apiIntegrationType');
        $query->setParameter('apiIntegrationType', $apiIntegrationType);
        $query = $query->getQuery();

        return $query->execute();
    }
}
