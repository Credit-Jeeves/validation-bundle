<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class JobRelatedOrderRepository extends EntityRepository
{
    /**
     * @param array $groups
     * @param \DateTime $date
     * @return JobRelatedOrder[]
     */
    public function getFailedPushJobsToExternalApi(array $groups, \DateTime $date)
    {
        if (empty($groups)) {
            return [];
        }

        return $this->createQueryBuilder('job_related')
            ->innerJoin('job_related.job', 'job')
            ->innerJoin('job_related.order', 'order')
            ->innerJoin('order.operations', 'operations')
            ->innerJoin('operations.contract', 'contract')
            ->innerJoin('contract.group', 'gr')
            ->where('gr.id IN (:groupId)')
            ->andWhere('job.command = :command')
            ->andWhere('job.state = :failed')
            ->andWhere('DATE(job_related.createdAt) = :date')
            ->setParameter('groupId', $groups)
            ->setParameter('failed', Job::STATE_FAILED)
            ->setParameter('command', 'external_api:payment:push')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->execute();
    }
}
