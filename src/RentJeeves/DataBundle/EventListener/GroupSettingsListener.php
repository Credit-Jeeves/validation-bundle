<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class GroupSettingsListener
{
    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        /** @var $entity GroupSettings */
        $entity = $eventArgs->getEntity();
        if (!$entity instanceof GroupSettings) {
            return;
        }
        if (null === $entity->getFeeACH()) {
            $entity->setFeeACH((float) $this->feeACH);
        }
        if (null === $entity->getFeeCC()) {
            $entity->setFeeCC((float) $this->feeCC);
        }
    }
}
