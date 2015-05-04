<?php

namespace RentJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\Address;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * Controls that only one address is default.
 *
 * @Service("data.event_listener.address")
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
class AddressListener
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Address) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $addresses = $em->getRepository('DataBundle:Address')->findBy(
            ['user' => $entity->getUser(), 'isDefault' => true]
        );

        // first payment account has to be active
        if (count($addresses) == 0) {
            $entity->setIsDefault(true);

            return;
        }

        if (!$entity->getIsDefault()) {
            return;
        }

        foreach ($addresses as $address) {
            $address->setIsDefault(false);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof Address) {
            return;
        }

        if (!$entity->getIsDefault()) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $em->getRepository('DataBundle:Address')->resetDefaults($entity->getUser()->getId());
    }
}
