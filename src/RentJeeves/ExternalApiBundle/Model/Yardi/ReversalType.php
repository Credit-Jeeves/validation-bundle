<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

class ReversalType
{
    /** @var Order */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"reversedPayment"})
     * @Serializer\Type("string")
     *
     * @return string
     */
    public function getReversal()
    {
        /** @var YardiSettings $yardiSettings */
        $yardiSettings = $this->order->getContract()->getHolding()->getYardiSettings();

        return $yardiSettings->getReversalType($this->order);
    }
}
