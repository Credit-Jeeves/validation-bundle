<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;
use Doctrine\ORM\Event\PreUpdateEventArgs;

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
 *         "event"="preRemove",
 *         "method"="preRemove"
 *     }
 * )
 */
class UnitListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postSoftDelete(LifecycleEventArgs $eventArgs)
    {
        $this->removeContractsWaiting($eventArgs);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Unit) {
            return;
        }

        $property = $entity->getProperty();
        $propertyAddress = $property->getPropertyAddress();
        if ($propertyAddress->isSingle() && count($property->getUnits()) > 1) {
            throw new LogicException('Standalone property can not have units');
        }
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Unit) {
            return;
        }

        if (!$eventArgs->hasChangedField('name')) {
            return;
        }
        $property = $entity->getProperty();
        $propertyAddress = $property->getPropertyAddress();
        if ($propertyAddress->isSingle() && $entity->getActualName() !== Unit::SINGLE_PROPERTY_UNIT_NAME) {
            $entity->setName(Unit::SINGLE_PROPERTY_UNIT_NAME);
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $this->removeContractsWaiting($eventArgs);
    }

    /**
     * Documentation link https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
     *
     * @param LifecycleEventArgs $eventArgs
     */
    protected function removeContractsWaiting(LifecycleEventArgs $eventArgs)
    {
        /** @var $unit Unit */
        $unit = $eventArgs->getEntity();
        if (!$unit instanceof Unit) {
            return;
        }

        $eventArgs->getEntityManager()->getRepository('RjDataBundle:ContractWaiting')->deleteByUnit($unit);
    }
}
