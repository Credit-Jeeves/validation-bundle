<?php

namespace RentJeeves\ExternalApiBundle\Model;

use CreditJeeves\DataBundle\Enum\OrderType;
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
     * @Serializer\Groups({"soapYardiRequest"})
     */
    protected $type;

    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Order")
     * @Serializer\Groups({"soapYardiRequest"})
     */
    protected $detail;

    public function __construct(YardiSettings $yardiSettings, Order $order = null)
    {
        $this->setDetail($order);

        $orderType = $order->getType();
        $this->type = self::getType($yardiSettings, $orderType);
    }

    public static function getType(YardiSettings $yardiSettings, $orderType)
    {
        switch ($orderType) {
            case OrderType::HEARTLAND_BANK:
                $type = $yardiSettings->getPaymentTypeACH();
                break;
            case OrderType::HEARTLAND_CARD:
                $type = $yardiSettings->getPaymentTypeCC();
                break;
            default:
                throw new Exception(
                    sprintf(
                        "Order type '%s' can't be process. Provide yardi settings for it.",
                        $orderType
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
