<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Entity\Group;

class JobRelatedOrderRepository extends EntityRepository
{
    /**
     * @param Group $group
     * @param \DateTime $data
     * @return JobRelatedOrder[]
     */
    public function getFailedPushJobsToExternalApi(Group $group, \DateTime $date)
    {
        return $this->createQueryBuilder('job_related')
            ->innerJoin('job_related.job', 'job')
            ->innerJoin('job_related.order', 'order')
            ->innerJoin('order.operations', 'operations')
            ->innerJoin('operations.contract', 'contract')
            ->innerJoin('contract.group', 'gr')
            ->where('gr.id = :groupId')
            ->andWhere('job.command = :command')
            ->andWhere('job.state = :failed')
            ->andWhere('DATE(job_related.createdAt) = :date')
            ->setParameter('groupId', $group->getId())
            ->setParameter('failed', Job::STATE_FAILED)
            ->setParameter('command', 'external_api:payment:push')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->execute();
    }
}

