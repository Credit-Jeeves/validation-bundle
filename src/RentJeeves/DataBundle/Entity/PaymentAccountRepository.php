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

    /**
     * @param int $id
     *
     * @return PaymentAccount
     */
    public function findByDepositAccountId($id)
    {
        return $this->createQueryBuilder('pa')
            ->select('pa')
            ->join('pa.depositAccounts', 'da')
            ->where('da.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }
}
