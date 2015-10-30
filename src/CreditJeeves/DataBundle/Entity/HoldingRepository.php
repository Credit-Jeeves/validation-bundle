<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

class HoldingRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForHoldingsWithResManSettings()
    {
        return $this->createQueryBuilder('holding')
            ->innerJoin('holding.resManSettings', 'resManSettings')
            ->innerJoin('holding.propertyMapping', 'propertyMapping')
            ->where('holding.apiIntegrationType = :resManSettings')
            ->setParameter('resManSettings', ApiIntegrationType::RESMAN);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForHoldingsWithMriSettings()
    {
        return $this->createQueryBuilder('holding')
            ->innerJoin('holding.mriSettings', 'mriSettings')
            ->innerJoin('holding.propertyMapping', 'propertyMapping')
            ->where('holding.apiIntegrationType = :mri')
            ->setParameter('mri', ApiIntegrationType::MRI);
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingBalanceYardi()
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.yardiSettings', 'ys')
            ->where('ys.syncBalance = 1')
            ->andWhere('h.apiIntegrationType = :yardi')
            ->setParameter('yardi', ApiIntegrationType::YARDI_VOYAGER)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingBalanceMRI()
    {
        return $this->createQueryBuilder('holding')
            ->innerJoin('holding.mriSettings', 'mriSettings')
            ->where('holding.apiIntegrationType = :mri')
            ->setParameter('mri', ApiIntegrationType::MRI)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingBalanceAMSI()
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.amsiSettings', 's')
            ->where('h.apiIntegrationType = :amsi')
            ->andWhere('s.syncBalance = 1')
            ->setParameter('amsi', ApiIntegrationType::AMSI)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingBalanceResMan()
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.resManSettings', 's')
            ->where('h.apiIntegrationType = :resman')
            ->andWhere('s.syncBalance = 1')
            ->setParameter('resman', ApiIntegrationType::RESMAN)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingRentMRI()
    {
        return $this->createQueryBuilder('holding')
            ->innerJoin('holding.mriSettings', 'mriSettings')
            ->where('holding.apiIntegrationType = :mri')
            ->andWhere('holding.useRecurringCharges = 1')
            ->setParameter('mri', ApiIntegrationType::MRI)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingRentAMSI()
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.amsiSettings', 's')
            ->where('h.apiIntegrationType = :amsi')
            ->andWhere('h.useRecurringCharges = 1')
            ->setParameter('amsi', ApiIntegrationType::AMSI)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingRentYardi()
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.yardiSettings', 'ys')
            ->where('h.useRecurringCharges = 1')
            ->andWhere('h.apiIntegrationType = :yardi')
            ->setParameter('yardi', ApiIntegrationType::YARDI_VOYAGER)
            ->getQuery()
            ->iterate();
    }

    /**
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function findHoldingsForUpdatingRentResMan()
    {
        return $this->createQueryBuilder('h')
            ->innerJoin('h.resManSettings', 's')
            ->where('h.apiIntegrationType = :resman')
            ->andWhere('h.useRecurringCharges = 1')
            ->setParameter('resman', ApiIntegrationType::RESMAN)
            ->getQuery()
            ->iterate();
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
     * @param int $firstId
     * @param int $lastId
     *
     * @return Holding[]
     */
    public function findHoldingsByRangeIds($firstId, $lastId)
    {
        return $this->createQueryBuilder('h')
            ->where('h.id >= :firstId AND h.id <= :lastId')
            ->setParameter('firstId', $firstId)
            ->setParameter('lastId', $lastId)
            ->getQuery()
            ->execute();
    }
}
