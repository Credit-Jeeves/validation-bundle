<?php

namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;

/**
 * @method AciImportProfileMap|AciImportProfileMap[] find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 */
class AciImportProfileMapRepository extends EntityRepository
{
    /**
     * @param Holding $holding
     *
     * @return AciImportProfileMap[]
     */
    public function findAllByHolding(Holding $holding)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.contracts', 'c')
            ->leftJoin('p.group', 'g')
            ->where('g.holding = :holding')
            ->orWhere('c.holding = :holding')
            ->setParameter('holding', $holding)
            ->getQuery()
            ->getResult();
    }
}
