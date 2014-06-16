<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;

class PaymentAccountRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return PaymentAccount
     */
    public function findOneWithGroupAddress($id)
    {
        $queryBuilder = $this->createQueryBuilder('pa');
        $queryBuilder->leftJoin('pa.address', 'a');
        $queryBuilder->where('pa.id = :id');
        $queryBuilder->setParameter('id', $id);

        return $queryBuilder->getQuery()->getSingleResult();
    }
}
