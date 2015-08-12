<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;

class BillingAccountRepository extends EntityRepository
{
    /**
     * @param Group $group
     *
     * @return BillingAccount[]
     */
    public function deactivateAccounts($group)
    {
        return $this->createQueryBuilder('c')
            ->update()
            ->set('c.isActive', 0)
            ->where('c.group = :group')
            ->andWhere('c.isActive = 1')
            ->setParameter('group', $group)
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
    protected function findOneOrNullByToken($token)
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
