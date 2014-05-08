<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Entity\Landlord;
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
 *
 */
class PropertyListener
{
    protected $user;

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container", required = true)
     * })
     */
    public function __construct($container)
    {
        $this->user = $container;
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
            $unit = new Unit();
            $unit->setProperty($entity);
            $unit->setName(UNIT::SINGLE_PROPERTY_UNIT_NAME);
            $entity->addUnit($unit);
            $eventArgs->getEntityManager()->persist($unit);
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
}
