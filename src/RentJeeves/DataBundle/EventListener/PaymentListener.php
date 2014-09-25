<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Payment;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentStatus;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 *
 * @Service("data.event_listener.payment")
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
 *         "event"="postUpdate",
 *         "method"="postUpdate"
 *     }
 * )
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="onFlush",
 *         "method"="onFlush"
 *     }
 * )
 */
class PaymentListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof Payment) {
            $entity->checkContract();
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if (($entity instanceof Contract) &&
            ContractStatus::DELETED == $entity->getStatus() &&
            ($payment = $entity->getActivePayment()) &&
            PaymentStatus::CLOSE != $payment->getStatus()
        ) {
            $payment->setStatus(PaymentStatus::CLOSE);
            $em->persist($payment);
            $em->flush($payment);
        }
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Payment) {
                $oldValue = $entity->getStatus();
                $entity->setStatus(PaymentStatus::CLOSE);
                $em->persist($entity);
                $uow->propertyChanged($entity, 'status', $oldValue, PaymentStatus::CLOSE);
                $uow->scheduleExtraUpdate($entity, array('status' => array($oldValue, PaymentStatus::CLOSE)));
            }
        }
    }
}
