<?php
namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\Event\LifecycleEventArgs;
//use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * @Service("data.event_listener.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist" 
 *     }
 * )
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postUpdate",
 *         "method"="postUpdate"
 *     }
 * )
 */
class OrderListener
{
    /**
     * Two main goals for this method:
     * 1. Set paidTo for contract
     * 2. Set daysLate for order
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            //echo __METHOD__;
            //$this->setScore($entity, $em);
        }
    }

    /**
     * This method for emails
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Order) {
            //echo __METHOD__;
            //$this->checkCompleted($entity, $em);
        }
        
    }
}
