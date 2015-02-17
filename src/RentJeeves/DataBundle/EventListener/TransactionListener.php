<?php
namespace RentJeeves\DataBundle\EventListener;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;

/**
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

    public function postPersist(LifecycleEventArgs $event)
    {
        /** @var Heartland $transaction */
        $transaction = $event->getEntity();
        if (!$transaction instanceof Heartland) {
            return;
        }
        $this->manageApiSynchronization($event);
    }

    public function manageApiSynchronization(LifecycleEventArgs $event)
    {
        /** @var Heartland $transaction */
        $transaction = $event->getEntity();
        if (!$transaction->getIsSuccessful() || !$transaction->getBatchId() || !$transaction->getTransactionId()) {
            return;
        }

        /** @var AccountingPaymentSynchronizer $paymentSync */
        $paymentSync = $this->container->get('accounting.payment_sync');
        $paymentSync->manageOrderToApi($transaction->getOrder());
    }
}
