<?php

namespace RentJeeves\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * Service "data.event_listener.deposit_account"
 */
class DepositAccountListener
{
    const PROFIT_STARS_CONTRACTS_PER_PAGE = 100;

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }

        $this->registerToProfitStars($entity, $eventArgs->getEntityManager());
        $eventArgs->getEntityManager()->flush(); // without this new jobs will not be flushed in postPersist
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }

        if ($eventArgs->hasChangedField('status')) {
            $this->registerToProfitStars($entity, $eventArgs->getEntityManager());
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }
        foreach ($em->getUnitOfWork()->getScheduledEntityInsertions() as $insertion) {
            if ($insertion instanceof Job) {
                $em->flush($insertion); // flush cannot be used in preUpdate -- so flush new jobs here
            }
        }
    }

    /**
     * @param DepositAccount $depositAccount
     * @param EntityManagerInterface $em
     */
    protected function registerToProfitStars(DepositAccount $depositAccount, EntityManagerInterface $em)
    {
        if (DepositAccountStatus::DA_COMPLETE === $depositAccount->getStatus() &&
            PaymentProcessor::PROFIT_STARS === $depositAccount->getPaymentProcessor()
        ) {
            $count = $em->getRepository('RjDataBundle:Contract')->getCountActiveWithGroup($depositAccount->getGroup());
            $pages = ceil($count/self::PROFIT_STARS_CONTRACTS_PER_PAGE);
            for ($page = 1; $page <= $pages; $page++) {
                $job = new Job(
                    'renttrack:payment-processor:profit-stars:register-contracts',
                    [$depositAccount->getId(), $page, self::PROFIT_STARS_CONTRACTS_PER_PAGE]
                );
                $em->getUnitOfWork()->persist($job); // flush cannot be used in preUpdate - causes infinite loop
            }
        }
    }
}
