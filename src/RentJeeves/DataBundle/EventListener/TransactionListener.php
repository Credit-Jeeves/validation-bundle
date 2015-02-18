<?php
namespace RentJeeves\DataBundle\EventListener;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Enum\TransactionStatus;

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
     */
    public $container;

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        /** @var Heartland $transaction */
        $transaction = $event->getEntity();
        if (!$transaction instanceof Heartland) {
            return;
        }
        $this->manageAccountingSynchronization($transaction);
    }

    /**
     * @param Heartland $transaction
     */
    public function manageAccountingSynchronization(Heartland $transaction)
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

        $accountingPaymentSync = $this->container->get('accounting.payment_sync');
        $accountingPaymentSync->sendOrderToAccountingSystem($transaction->getOrder());
    }
}
