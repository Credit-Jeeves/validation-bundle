<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;

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
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist"
 *     }
 * )
 */
class PropertyListener
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Property) {
            return;
        }

        if ($entity->hasUnits() || $entity->hasGroups()) {
            return;
        }

        if ($entity->getIsSingle() !== true) {
            return;
        }

        $this->createStandaloneUnit($eventArgs);
    }


    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Property) {
            return;
        }

        if (!$eventArgs->hasChangedField('isSingle')) {
            return;
        }

        if (($entity->hasUnits() || $entity->hasGroups()) && $eventArgs->getOldValue('isSingle') !== null) {
            throw new LogicException('You can not modify standalone property');
        }

        if ($entity->getIsSingle() == true) {
            $this->createStandaloneUnit($eventArgs);
        }
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Property) {
            return;
        }

        if ($entity->getIsSingle() == true) {
            $units = $entity->getUnits();
            if (count($units) > 1) {
                throw new LogicException(
                    sprintf('Standalone property "%s" has more than one unit.', $entity->getAddress())
                );
            }
            if ($unit = $units->first()) {
                $eventArgs->getEntityManager()->flush($unit);
            }
        }
    }

    protected function createStandaloneUnit($eventArgs)
    {
        /**
         * @var $property Property
         */
        $property = $eventArgs->getEntity();
        /**
         * @var $unit Unit
         */
        $unit = $property->getUnits()->first();

        if ($property->getUnits()->count() === 1 && $unit->getActualName() === Unit::SINGLE_PROPERTY_UNIT_NAME) {
            return;
        }
        $unit = Property::getNewSingleUnit($property);
        $eventArgs->getEntityManager()->persist($unit);
    }
}
