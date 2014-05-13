<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="preUpdate",
 *         "method"="preUpdate"
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
        $this->checkContract($entity);
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Contract) {
            return;
        }
        $this->checkContract($entity);
    }

    public function checkContract(Contract $contract)
    {
        $property = $contract->getProperty();
        if ($property->isSingle() && $unit = $property->getSingleUnit()) {
            $contract->setUnit($unit);
            return;
        }

        $unit = $contract->getUnit();
        if ($unit instanceof Unit && $unit->getProperty()->getId() == $property->getId()) {
            return;
        }

        throw new LogicException('Invalid contract parameters');
    }
}
