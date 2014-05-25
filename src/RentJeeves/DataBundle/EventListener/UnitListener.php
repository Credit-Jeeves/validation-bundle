<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;

/**
 * @Service("data.event_listener.unit")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postSoftDelete",
 *         "method"="postSoftDelete"
 *     }
 * )
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist"
 *     }
 * )
 */
class UnitListener
{
    public function postSoftDelete(LifecycleEventArgs $eventArgs)
    {
        /**
         * @var $unit Unit
         */
        $unit = $eventArgs->getEntity();
        if (!$unit instanceof Unit) {
            return;
        }
        $unit->deleteAllWaitingContracts($eventArgs);
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Unit) {
            return;
        }

        $property = $entity->getProperty();
        if ($property->isSingle() && count($property->getUnits()) > 1) {
            throw new LogicException('Standalone property can not have units');
        }
    }
}
