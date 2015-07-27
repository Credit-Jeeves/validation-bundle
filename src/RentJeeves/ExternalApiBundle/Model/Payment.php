<?php

namespace RentJeeves\ExternalApiBundle\Model;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\DataBundle\Entity\YardiSettings;
use Exception;

class Payment
{
    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed"})
     */
    protected $type;

    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Order")
     * @Serializer\Groups({"soapYardiRequest", "soapYardiReversed"})
     */
    protected $detail;

    public function __construct(YardiSettings $yardiSettings, Order $order = null)
    {
        $this->setDetail($order);

        $this->type = self::getType($yardiSettings, $order);
    }

    public static function getType(YardiSettings $yardiSettings, Order $order)
    {
        // When reversing any type of receipt, always use a payment type of “Other”.
        if (in_array($order->getStatus(), [OrderStatus::RETURNED, OrderStatus::REFUNDED])) {
            return YardiSettings::REVERSAL_PAYMENT_TYPE;
        }

        $type = $yardiSettings->getOrderPaymentType($order);
        if (!$type) {
            throw new Exception(
                sprintf(
                    "Order type '%s' can't be process. Provide yardi settings for it.",
                    $order->getPaymentType()
                )
            );
        }

        return self::formatType($type);
    }

    public static function formatType($type)
    {
        return ucfirst($type);
    }

    /**
     * @param Order $detail
     */
    public function setDetail(Order $detail)
    {
        $this->detail = $detail;
    }

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
