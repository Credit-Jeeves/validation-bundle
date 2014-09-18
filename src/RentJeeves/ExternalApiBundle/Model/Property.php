<?php

namespace RentJeeves\ExternalApiBundle\Model;

use CreditJeeves\DataBundle\Entity\Order;
use JMS\Serializer\Annotation as Serializer;

class Property
{
    /**
     * @Serializer\SerializedName("RT_Customer")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\RtCustomer")
     * @Serializer\Groups({"soapYardiRequest"})
     */
    protected $rtCustomer;

    public function __construct($orders = null)
    {
        $this->rtCustomer = new RtCustomer($orders);
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
