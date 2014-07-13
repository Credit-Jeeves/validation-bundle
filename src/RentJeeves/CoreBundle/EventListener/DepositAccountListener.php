<?php

namespace RentJeeves\CoreBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\DepositAccount;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Moved from RjDataBundle because #RT-407 Them mail problem RJ parameters: payment_card_fee and payment_bank_fee
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
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes = {
 *         "event"="postLoad",
 *         "method"="postLoad"
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
     * @Inject("%payment_card_fee%", required = true)
     */
    public $feeCC;

    /**
     * @Inject("%payment_bank_fee%", required = true)
     */
    public $feeACH;

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

        if ($eventArgs->hasChangedField('feeACH') && $entity->getFeeACH() == $this->feeACH) {
            $eventArgs->setNewValue('feeACH', null);
        }

        if ($eventArgs->hasChangedField('feeCC') && $entity->getFeeCC() == $this->feeCC) {
            $eventArgs->setNewValue('feeCC', null);
        }

        if ($eventArgs->hasChangedField('merchantName') && !$eventArgs->getOldValue('merchantName')) {
            $this->sendEmail($entity);
        }
    }

    private function sendEmail(DepositAccount $entity)
    {
        if (!$entity->isComplete()) {
            return;
        }

        $group =  $entity->getGroup();

        if (!$group) {
            return;
        }

        $holding = $group->getHolding();

        if (!$holding) {
            return;
        }

        $usersAdminList = $holding->getHoldingAdmin();
        $users = $holding->getDealers();
        /** @var Mailer $mail */
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

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        /** @var $entity DepositAccount */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof DepositAccount) {
            return;
        }
        if (null === $entity->getFeeACH()) {
            $entity->setFeeACH((float)$this->feeACH);
        }
        if (null === $entity->getFeeCC()) {
            $entity->setFeeCC((float)$this->feeCC);
        }
    }
}
