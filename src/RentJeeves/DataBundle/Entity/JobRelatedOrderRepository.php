<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityRepository;

class JobRelatedOrderRepository extends EntityRepository
{
    /**
     * @param Holding $holding
     * @param \DateTime $data
     * @return JobRelatedOrder[]
     */
    public function getFailureOrder(Holding $holding, \DateTime $date)
    {
        return $this->createQueryBuilder('job_related')
            ->innerJoin('job_related.job', 'job')
            ->innerJoin('job_related.order', 'order')
            ->innerJoin('order.operations', 'operations')
            ->innerJoin('operations.group', 'group')
            ->innerJoin('group.holding', 'holding')
            ->where('holding.id = :holding')
            ->andWhere('job.state = :failed')
            ->andWhere('DATE(job_related.createdAt) = :date')
            ->setParameter('holding', $holding)
            ->setParameter('failed', Job::STATE_FAILED)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->execute();
    }
}

