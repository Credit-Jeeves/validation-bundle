<?php
namespace RentJeeves\DataBundle\EventListener;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use Symfony\Component\DependencyInjection\Container;

/**
 * I can't just inject service which I need because have error
 *   [Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException]
 *   Circular reference detected for service "doctrine.orm.default_entity_manager",
 *   path: "doctrine.orm.default_entity_manager -> doctrine.dbal.default_connect
 *   ion -> data.event_listener.transaction -> accounting.payment_sync".
 *
 * @DI\Service("data.event_listener.transaction")
 * @DI\Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postPersist",
 *         "method"="postPersist"
 *     }
 * )
 */
class TransactionListener
{
    /**
     * @DI\Inject("service_container", required = true)
     *
     * @var Container
     */
    public $container;

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        /** @var Transaction $transaction */
        $transaction = $event->getEntity();
        if (!$transaction instanceof Transaction) {
            return;
        }

        $this->manageAccountingSynchronization($transaction);

        if (true === $this->canCreateJobReturnForAmsi($transaction)) {
            $this->addJobForPostReversalsToAmsi($transaction);
        }
    }

    /**
     * @param Transaction $transaction
     */
    public function manageAccountingSynchronization(Transaction $transaction)
    {
        if (!$transaction->getIsSuccessful() ||
            !$transaction->getBatchId() ||
            !$transaction->getTransactionId() ||
            $transaction->getStatus() !== TransactionStatus::COMPLETE
        ) {
            $message = "Don't send transaction(%s) to api, because some parameter is missing(return false):\n";
            $message .= "IsSuccessful(%s), BatchId(%s),  TransactionId(%s), TransactionStatus(%s)";
            $this->container->get('logger')->debug(
                sprintf(
                    $message,
                    $transaction->getId(),
                    $transaction->getIsSuccessful(),
                    $transaction->getBatchId(),
                    $transaction->getTransactionId(),
                    $transaction->getStatus()
                )
            );

            return;
        }

        if (!$order = $transaction->getOrder()) {
            $this->container->get('logger')->debug(
                sprintf(
                    "Transaction %s does not have order, we don't send it to external API",
                    $transaction->getId()
                )
            );

            return;
        }

        if (!$contract = $order->getContract()) {
            $this->container->get('logger')->debug(
                sprintf(
                    "Transaction %s does not have contract, we don't send it to external API",
                    $transaction->getId()
                )
            );

            return;
        }

        /** @var AccountingPaymentSynchronizer $accountingPaymentSync */
        $accountingPaymentSync = $this->container->get('accounting.payment_sync');
        $accountingPaymentSync->setDebug(true);

        if ($accountingPaymentSync->isAllowedToSend($contract->getHolding())) {
            $accountingPaymentSync->createJob($transaction->getOrder());
        }
    }

    /**
     * @param Transaction $transaction
     */
    protected function addJobForPostReversalsToAmsi(Transaction $transaction)
    {
        $order = $transaction->getOrder();

        $executeTime = new \DateTime();
        $executeTime->modify('+5 minutes');

        $job = new Job('api:accounting:amsi:return-payment', [$order->getId()]);
        $job->setExecuteAfter($executeTime);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush($job);
    }

    /**
     * @param Transaction $transaction
     *
     * @return boolean
     */
    protected function canCreateJobReturnForAmsi(Transaction $transaction)
    {
        if ($transaction->getStatus() === TransactionStatus::REVERSED) {
            if ($contract = $transaction->getOrder()->getContract()) {
                if ($accountingSettings = $contract->getHolding()->getAccountingSettings()) {
                    $apiType = $accountingSettings->getApiIntegration();
                    if ($apiType === ApiIntegrationType::AMSI) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}
