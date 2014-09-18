<?php

namespace RentJeeves\ExternalApiBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Entity\Order;

class Payment
{

    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"soapYardiRequest"})
     */
    protected $type = "Check";

    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Order")
     * @Serializer\Groups({"soapYardiRequest"})
     */
    protected $detail;

    public function __construct(Order $order = null)
    {
        $this->setDetail($order);
    }

    /**
     * @param Order $detail
     */
    public function setDetail(Order $detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return Order
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param string $typeKey
     */
    public function setTypeKey($typeKey)
    {
        $this->typeKey = $typeKey;
    }

    /**
     * @return string
     */
    public function getTypeKey()
    {
        return $this->typeKey;
    }

    /**
     * @param string $typeValue
     */
    public function setTypeValue($typeValue)
    {
        $this->typeValue = $typeValue;
    }

    /**
     * @return string
     */
    public function getTypeValue()
    {
        return $this->typeValue;
    }
}
