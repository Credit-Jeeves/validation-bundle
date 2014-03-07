<?php

namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use RentJeeves\DataBundle\Entity\DepositAccount;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("data.event_listener.deposit_account")
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
class DepositAccountListener
{
    /**
     * @Inject("service_container", required = true)
     */
    public $container;

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        /** @var $entity DepositAccount */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }

        $this->sendEmail($entity);
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        /** @var $entity DepositAccount */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }

        if ($eventArgs->hasChangedField('merchantName') && !$eventArgs->getOldValue('merchantName')) {
            $this->sendEmail($entity);
        }
    }

    private function sendEmail($entity)
    {
        if (!$entity->isComplete()) {
            return;
        }

        $usersAdminList = $entity->getGroup()->getHolding()->getHoldingAdmin();
        $users = $entity->getGroup()->getHolding()->getDealers();
        $group =  $entity->getGroup();
        $mail = $this->container->get('project.mailer');

        if (empty($usersAdminList) && $users->count() <= 0) {
            return;
        }

        if (empty($usersAdminList) && $users->count() > 0) {
            $user = $users->first();
            $mail->merchantNameSetuped($user, $group);
            return;
        }

        foreach ($usersAdminList as $user) {
            $mail->merchantNameSetuped($user, $group);
        }
    }
}
