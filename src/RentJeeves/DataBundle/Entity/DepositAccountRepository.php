<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;

class DepositAccountRepository extends EntityRepository
{
    /**
     * TODO: change this in RT-1719: HPS: Move PaymentAccount-Deposit_account relation to PaymentAccount-MerchantName
     * @param PaymentAccount $paymentAccount
     *
     * @return Array
     */
    public function completeByPaymentAccountAndDepositAccount(
        PaymentAccount $paymentAccount,
        DepositAccount $depositAccount
    ) {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->join('d.paymentAccounts', 'p')
            ->where('d.status = :status')
            ->andWhere('p.id = :payment_account_id')
            ->andWhere('d.group = :group')
            ->andWhere('d.merchantName = :merchant_name')
            ->andWhere('d.paymentProcessor = :payment_processor')
            ->setParameter('status', DepositAccountStatus::DA_COMPLETE)
            ->setParameter('payment_account_id', $paymentAccount->getId())
            ->setParameter('group', $depositAccount->getGroup())
            ->setParameter('merchant_name', $depositAccount->getMerchantName())
            ->setParameter('payment_processor', $depositAccount->getPaymentProcessor())
            ->getQuery()
            ->execute();
    }

    /**
     * TODO: change this in RT-1719: HPS: Move PaymentAccount-Deposit_account relation to PaymentAccount-MerchantName
     * @param PaymentAccount $paymentAccount
     * @param Group $group
     * @param string $paymentProcessor
     *
     * @return Array
     */
    public function getAssociatedForPaymentAccountAndGroup(
        PaymentAccount $paymentAccount,
        Group $group
    ) {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->join('d.paymentAccounts', 'p')
            ->where('p.id = :payment_account_id')
            ->andWhere('d.group = :group')
            ->andWhere('d.paymentProcessor = p.paymentProcessor')
            ->setParameter('payment_account_id', $paymentAccount->getId())
            ->setParameter('group', $group)
            ->getQuery()
            ->execute();
    }
}
