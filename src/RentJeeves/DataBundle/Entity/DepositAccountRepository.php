<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;

class DepositAccountRepository extends EntityRepository
{
    /**
     * @param PaymentAccount $paymentAccount
     *
     * @return Array
     */
    public function completeByPaymentAccount(PaymentAccount $paymentAccount)
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->join('d.paymentAccounts', 'p')
            ->join('d.group', 'g')
            ->where('d.status = :status')
            ->andWhere('p.id = :payment_account_id')
            ->setParameter('status', DepositAccountStatus::DA_COMPLETE)
            ->setParameter('payment_account_id', $paymentAccount->getId())
            ->getQuery()
            ->execute();
    }
}
