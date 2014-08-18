<?php

namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;

/**
 * @DI\Service("data.event_listener.contract")
 * @DI\Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist"
 *     }
 * )
 */
class HeartlandTransactionListener
{
    /**
     * @DI\Inject("service_container", required = true)
     */
    public $container;

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $transaction = $eventArgs->getEntity();
        if (!$transaction instanceof HeartlandTransaction) {
            return;
        }

        $order = $transaction->getOrder();

        if ($transaction->getIsSuccessful() &&
            OrderType::HEARTLAND_CARD == $order->getType() &&
            OrderStatus::COMPLETE == $order->getStatus()
        ) {
            $batchDate = new DateTime();
            $transaction->setBatchDate($batchDate);
            $businessDaysCalc = $this->container->get('business_days_calculator');
            $transaction->setDepositDate($businessDaysCalc->getCreditCardBusinessDate($batchDate));
        }
    }
}
