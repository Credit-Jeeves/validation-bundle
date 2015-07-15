<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Entity\GroupSettings;

class GroupSettingsListener
{
    /**
     * @var float
     */
    public $defaultFeeCC;

    /**
     * @var float
     */
    public $defaultFeeACH;

    public function __construct($feeCC, $feeACH)
    {
        $this->defaultFeeCC = (float) $feeCC;
        $this->defaultFeeACH = (float) $feeACH;
    }

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
            $entity->setFeeACH($this->defaultFeeACH);
        }
        if (null === $entity->getFeeCC()) {
            $entity->setFeeCC($this->defaultFeeCC);
        }
    }
}
