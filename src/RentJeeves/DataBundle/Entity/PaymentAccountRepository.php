<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CoreBundle\Traits\DateCommon;
use Doctrine\ORM\Query\Expr;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentStatus;

class PaymentAccountRepository extends EntityRepository
{
    use DateCommon;

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
     * @return array
     */
    public function collectCreditTrackToJobs()
    {
        $date = new DateTime();
        $query = $this->createQueryBuilder('pa');
        $query->innerJoin('pa.creditTrackUserSetting', 'us');
        $query->andWhere('DATE(us.creditTrackEnabledAt) < :date'); //Payment which setup today must not be executed
        $query->setParameter('date', $date->format('Y-m-d'));
        $query->andWhere('DAY(us.creditTrackEnabledAt) IN (:dueDays)');
        $query->setParameter('dueDays', $this->getDueDays(0, $date));

        $paymentAccounts = $query->getQuery()->execute();

        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $jobs = array();
        /** @var PaymentAccount $paymentAccount */
        foreach ($paymentAccounts as $paymentAccount) {
            $job = new Job('payment:pay', array('--app=rj'));
            $relatedEntity = new JobRelatedCreditTrack();
            $relatedEntity->setCreditTrackPaymentAccount($paymentAccount);
            $job->addRelatedEntity($relatedEntity);
            $em->persist($jobs[] = $job);
        }
        $em->flush();

        return $jobs;
    }

    /**
     * @todo: After adding replace this function to $repo->findOneBy(['token' => $token]);
     *
     * @param string $token
     *
     * @return PaymentAccount|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneOrNullByToken($token)
    {
        return $this->createQueryBuilder('pa')
            ->where('pa.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Tenant $tenant
     * @return PaymentAccount[]
     */
    public function getActivePaymentAccountsForTenant(Tenant $tenant)
    {
        return $this->createQueryBuilder('pa')
            ->innerJoin('pa.user', 'u')
            ->innerJoin('u.contracts', 'c')
            ->innerJoin('c.group', 'g')
            ->innerJoin('g.groupSettings', 'gs', Expr\Join::WITH, 'gs.paymentProcessor = pa.paymentProcessor')
            ->where('pa.user = :tenant')
            ->andWhere('c.status != :statusDeleted')
            ->andWhere('c.status != :statusFinished')
            ->setParameters([
                'tenant' => $tenant,
                'statusDeleted' => ContractStatus::DELETED,
                'statusFinished' => ContractStatus::FINISHED,
            ])
            ->groupBy('pa.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Tenant $tenant
     * @param Contract $contract
     * @return PaymentAccount[]
     */
    public function getPaymentAccountsForTenantByContract(Tenant $tenant, Contract $contract)
    {
        $paymentProcessor = $contract->getGroupSettings()->getPaymentProcessor();
        $isDisabledCreditCard = $contract->getGroup()->isDisableCreditCard();
        $isAllowDebitCard = $contract->getGroupSettings()->isAllowedDebitFee();

        $query = $this->createQueryBuilder('pa')
            ->innerJoin('pa.user', 'u')
            ->where('pa.user = :tenant')
            ->andWhere('pa.paymentProcessor = :paymentProcessor')
            ->setParameters([
                'tenant' => $tenant,
                'paymentProcessor' => $paymentProcessor
            ]);

        if ($isDisabledCreditCard) {
            $query
                ->andWhere('pa.type != :card')
                ->andWhere('pa.type != :debit_card')
                ->setParameter('card', PaymentAccountType::CARD)
                ->setParameter('debit_card', PaymentAccountType::DEBIT_CARD);
        } elseif (!$isAllowDebitCard) {
            $query
                ->andWhere('pa.type != :debit_card')
                ->setParameter('debit_card', PaymentAccountType::DEBIT_CARD);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param PaymentAccount $paymentAccount
     *
     * @return bool
     */
    public function isValidForDelete(PaymentAccount $paymentAccount)
    {
        $result = $this->createQueryBuilder('pa')
            ->leftJoin('pa.payments', 'p')
            ->leftJoin('pa.orders', 'o')
            ->where('pa.id = :id')
            ->andWhere('o.status = :orderPendingStatus OR p.status in (:paymentActiveStatuses)')
            ->setParameter('id', $paymentAccount->getId())
            ->setParameter('paymentActiveStatuses', [PaymentStatus::ACTIVE, PaymentStatus::FLAGGED])
            ->setParameter('orderPendingStatus', OrderStatus::PENDING)
            ->setMaxResults(1)
            ->getQuery()
            ->execute();

        if (empty($result)) {
            return true;
        }

        return false;
    }
}
