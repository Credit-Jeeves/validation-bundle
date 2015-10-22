<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistCard;

class DebitCardBinlistRepository extends EntityRepository
{
    /**
     * @param string $iin
     * @return DebitCardBinlist|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDebitCardByIin($iin)
    {
        return $this->createQueryBuilder('c')
            ->where('c.cardType = :debit')
            ->andWhere('c.iin LIKE :search')
            ->setMaxResults(1)
            ->setParameter('debit', BinlistCard::TYPE_DEBIT)
            ->setParameter('search', $iin . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
