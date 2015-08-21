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
     * @param array $holdingIds
     *
     * @return AciImportProfileMap[]
     */
    public function findAllByHoldingIds(array $holdingIds)
    {
        if (true === empty($holdingIds)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.contracts', 'c')
            ->leftJoin('p.group', 'g')
            ->where('g.holding IN (:holdingIds)')
            ->orWhere('c.holding IN (:holdingIds)')
            ->setParameter('holdingIds', $holdingIds)
            ->getQuery()
            ->getResult();
    }
}
