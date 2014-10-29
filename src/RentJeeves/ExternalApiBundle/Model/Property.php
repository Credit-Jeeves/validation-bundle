<?php

namespace RentJeeves\ExternalApiBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\YardiSettings;

class Property
{
    /**
     * @Serializer\SerializedName("RT_Customer")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\RtCustomer")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed"})
     */
    protected $rtCustomer;

    public function __construct(YardiSettings $yardiSettings, $orders = null)
    {
        $this->rtCustomer = new RtCustomer($yardiSettings, $orders);
    }

    /**
     * @param RtCustomer $rtCustomer
     */
    public function setRtCustomer(RtCustomer $rtCustomer)
    {
        $this->rtCustomer = $rtCustomer;
    }

    /**
     * @return RtCustomer
     */
    public function getRtCustomer()
    {
        return $this->rtCustomer;
    }
}
