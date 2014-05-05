<?php

namespace RentJeeves\DataBundle\EventListener;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Entity\Property;
use LogicException;
use RentJeeves\DataBundle\Entity\Unit;

/**
 * Controls adding new unit if property marked as single..
 *
 * @Service("data.event_listener.property")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="preUpdate",
 *         "method"="preUpdate"
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
class PropertyListener 
{
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Property) {
            return;
        }

        if ($eventArgs->hasChangedField('isSingle') &&
            (($entity->hasUnits() || $entity->hasGroups()) && $eventArgs->getOldValue('isSingle') !== null)
        ) {
            throw new LogicException('You can not modify standalone property');
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Property) {
            return;
        }


    }
} 
