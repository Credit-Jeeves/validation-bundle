<?php

namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Dealer as DealerEntity;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;

/**
 * @Service("data.event_listener.dealer.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes={
 *         "event"="preRemove",
 *         "method"="preRemove"
 *     }
 * )
 */
class Dealer
{
    /**
     * On this method we need fill api update table if user or his lead was updated
     * it's only for user each have dealer with invite code $dealerCode
     *
     * @param LifecycleEventArgs $eventArgs
     * @return bool
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $user = false;
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof DealerEntity) {
            if ($entity->canRemove() === false) {
                throw new \Exception('Please reassign leads before deleting a dealer.');
            }
        }
    }
}
