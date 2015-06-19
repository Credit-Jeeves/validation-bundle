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

    /**
     * @param integer $start
     * @param integer $limit
     * @param string $strategy
     * @return Holding[]
     */
    public function findHoldingsWithYardiSettings($start, $limit, $strategy = null)
    {
        $query = $this->createQueryBuilder('holding');
        $query->innerJoin('holding.yardiSettings', 'yardiSetting');
        $query->where('holding.apiIntegrationType = :yardi');
        $query->andWhere('yardiSetting.postPayments = 1');
        $query->setParameter('yardi', ApiIntegrationType::YARDI_VOYAGER);

        if ($strategy) {
            $query->andWhere('yardiSetting.synchronizationStrategy = :strategy');
            $query->setParameter('strategy', $strategy);
        }

        $query->setFirstResult($start);
        $query->setMaxResults($limit);
        $query = $query->getQuery();

        return $query->execute();
    }

    /**
     * @param string $apiIntegrationType
     *
     * @return Holding[]
     */
    public function findAllByApiIntegration($apiIntegrationType)
    {
        if (false === ApiIntegrationType::isValid($apiIntegrationType)) {
            throw new \InvalidArgumentException(sprintf('Incorrect API integration type "%s"', $apiIntegrationType));
        }

        return $this->createQueryBuilder('holding')
            ->where('holding.apiIntegrationType = :apiIntegrationType')
            ->setParameter('apiIntegrationType', $apiIntegrationType)
            ->getQuery()
            ->execute();
    }

    /**
     * @return Holding[]
     */
    public function findHoldingsForUpdatingBalanceResMan()
    {
        $query = $this->createQueryBuilder('h');
        $query->innerJoin('h.resManSettings', 's');
        $query->where('h.apiIntegrationType = :resman');
        $query->andWhere('s.syncBalance = 1');
        $query->setParameter('resman', ApiIntegrationType::RESMAN);
        $query = $query->getQuery();

        return $query->execute();
    }
}
