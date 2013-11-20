<?php

namespace RentJeeves\DataBundle\EventListener;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use RentJeeves\DataBundle\Entity\DepositAccount;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("deposit_account.event_listener.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="prePersist",
 *         "method"="prePersist"
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
        $em = $eventArgs->getEntityManager();
        /** @var $entity DepositAccount */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }

        $id = $entity->getId();
        /**
         *  return because we need only for create merchant name event
         */
        if (!empty($id)) {
            return;
        }

        $usersAdminList = $entity->getGroup()->getHolding()->getHoldingAdmin();

        if (empty($usersAdminList)) {
            return;
        }

        $mail = $this->container->get('project.mailer');
        $group =  $entity->getGroup();
        foreach ($usersAdminList as $user) {
            $mail->merchantNameSetuped($user, $group);
        }
    }
}
