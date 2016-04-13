<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan\Transaction;

use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Entity\Order;

class Payment
{
    const SCANNED_CHECK_RETURN_TYPE = 'Check';

    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $type;

    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\Type("CreditJeeves\DataBundle\Entity\Order")
     * @Serializer\Groups({"ResMan"})
     */
    protected $detail;

    public function __construct(Order $order = null)
    {
        $this->setDetail($order);

        $this->type = self::getType($order);
    }

    /**
     * @param Order $order
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function getType(Order $order)
    {
        $type = $order->getPaymentType();

        switch ($type) {
            case OrderPaymentType::CARD:
                $typeReturn = 'Credit Card';
                break;
            case OrderPaymentType::BANK:
                $typeReturn = 'ACH';
                break;
            case OrderPaymentType::SCANNED_CHECK:
                $typeReturn = self::SCANNED_CHECK_RETURN_TYPE;
                break;
            default:
                $message = 'Not supported type of order(%s) it must be %s or %s';
                throw new \Exception(
                    sprintf(
                        $message,
                        $order->getId(),
                        OrderPaymentType::CARD,
                        OrderPaymentType::BANK
                    )
                );
        }

        return $typeReturn;
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
