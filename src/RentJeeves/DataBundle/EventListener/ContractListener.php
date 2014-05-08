<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Unit;
use LogicException;

/**
 * @Service("data.event_listener.contract")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist"
 *     }
 * )
 */
class ContractListener
{
    /**
     * Checks contract to contain unit
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Contract) {
            return;
        }

        if ($entity->getUnit() instanceof Unit) {
            return;
        }

        $property = $entity->getProperty();
        if ($property->getIsSingle() == true && $unit = $property->getUnits()->first()) {
            $entity->setUnit($unit);
            return;
        }

        throw new LogicException('Unit for contract was not found');
    }
}
