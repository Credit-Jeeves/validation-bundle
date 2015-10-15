<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

class DepositAccountRepository extends EntityRepository
{
    /**
     * @param Tenant $tenant
     * @return DepositAccount[]
     */
    public function getHPSDepositAccountsUniqueByMerchantForTenantAndHoldings(Tenant $tenant, array $holdingIds = null)
    {
        $query = $this->createQueryBuilder('d')
            ->join('d.paymentAccounts', 'p')
            ->where('p.user = :tenant')
            ->andWhere('d.paymentProcessor = :payment_processor');

        if (!empty($holdingIds)) {
            $query->andWhere('d.holding in (:holdings)')
                ->setParameter('holdings', $holdingIds);
        }

        return $query
            ->groupBy('d.merchantName')
            ->setParameter('tenant', $tenant)
            ->setParameter('payment_processor', PaymentProcessor::HEARTLAND)
            ->getQuery()
            ->execute();
    }
}
