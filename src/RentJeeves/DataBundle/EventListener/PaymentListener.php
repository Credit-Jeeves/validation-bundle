<?php
namespace RentJeeves\DataBundle\EventListener;

use RentJeeves\DataBundle\Entity\Payment;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * @Service("contract.event_listener.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist" 
 *     }
 * )
 */
class PaymentListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Payment) {
            $this->checkContract();
        }
    }
}
