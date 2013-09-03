<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use RentJeeves\DataBundle\Entity\DepositAccount;
/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @Service("data.event_listener.doctrine")
 * @Tag("doctrine.event_listener", attributes = { "event" = "prePersist", "method" = "prePersist" })
 */
class Doctrine
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof DepositAccount) {

        }
    }
}
