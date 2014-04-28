<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use \RentJeeves\DataBundle\Entity\Unit;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("unit.event_listener")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postSoftDelete",
 *         "method"="postSoftDelete"
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
        $unit->deleteAllWaitingContracts($eventArgs);
    }
}
