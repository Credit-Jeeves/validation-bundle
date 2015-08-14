<?php

namespace RentJeeves\DataBundle\EventListener;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Entity\BillingAccount;

/**
 * Controls that only one payment account is active.
 *
 * @Service("data.event_listener.billing_account")
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
class BillingAccountListener
{
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof BillingAccount) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $billingAccounts = $em->getRepository('RjDataBundle:BillingAccount')
            ->findBy(
                [
                    'group' => $entity->getGroup(),
                    'paymentProcessor' => $entity->getPaymentProcessor(),
                    'isActive' => true
                ]
            );

        // first payment account has to be active
        if (count($billingAccounts) == 0) {
            $entity->setIsActive(true);

            return;
        }

        if (!$entity->getIsActive()) {
            return;
        }

        foreach ($billingAccounts as $account) {
            $account->setIsActive(false);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof BillingAccount) {
            return;
        }

        if (!$entity->getIsActive()) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $em->getRepository('RjDataBundle:BillingAccount')->deactivateAccounts($entity->getGroup());
    }
}
