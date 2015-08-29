<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;

class BillingAccountRepository extends EntityRepository
{
    /**
     * @param Group $group
     * @param string $paymentProcessor
     *
     * @return BillingAccount[]
     */
    public function deactivateAccounts(Group $group, $paymentProcessor)
    {
        return $this->createQueryBuilder('c')
            ->update()
            ->set('c.isActive', 0)
            ->where('c.group = :group')
            ->andWhere('c.isActive = 1')
            ->andWhere('c.paymentProcessor = :paymentProcessor')
            ->setParameter('group', $group)
            ->setParameter('paymentProcessor', $paymentProcessor)
            ->getQuery()
            ->execute();
    }

    /**
     * @todo: After adding replace this function to $repo->findOneBy(['token' => $token]);
     *
     * @param string $token
     *
     * @return BillingAccount|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneOrNullByToken($token)
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
