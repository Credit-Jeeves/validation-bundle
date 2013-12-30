<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BillingAccountRepository extends EntityRepository
{
    public function deactivateAccounts($group)
    {
        $query = $this->createQueryBuilder('c')
            ->update()
            ->set('c.isActive', 0)
            ->where('c.group = :group')
            ->andWhere('c.isActive = 1')
            ->setParameter('group', $group)
            ->getQuery();

        return $query->execute();
    }
}
