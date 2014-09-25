<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @Service("data.event_listener.payment_account")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="preSoftDelete",
 *         "method"="preSoftDelete"
 *     }
 * )
 */
class PaymentAccountListener
{
    public function preSoftDelete(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof PaymentAccount) {
            $oldValue = $entity->getToken();
            $entity->setToken(null);
            $em->persist($entity);
            $uow->propertyChanged($entity, 'token', $oldValue, null);
            $uow->scheduleExtraUpdate($entity, array('token' => array($oldValue, null)));
        }
    }
}
