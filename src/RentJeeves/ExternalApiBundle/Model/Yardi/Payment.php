<?php

namespace RentJeeves\ExternalApiBundle\Model\Yardi;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\DataBundle\Entity\YardiSettings;

class Payment
{
    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     */
    protected $type;

    /**
     * @Serializer\SerializedName("Detail")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\Yardi\PaymentDetail")
     * @Serializer\Groups({"baseRequest", "withPostMonth", "reversedPayment"})
     */
    protected $detail;

    public function __construct(YardiSettings $yardiSettings, Order $order = null)
    {
        $this->setDetail(new PaymentDetail($order));

        $this->type = self::getType($yardiSettings, $order);
    }

    /**
     * @param YardiSettings $yardiSettings
     * @param Order $order
     * @return string
     * @throws \Exception
     */
    public static function getType(YardiSettings $yardiSettings, Order $order)
    {
        // When reversing any type of receipt, always use a payment type of “Other”.
        if (in_array($order->getStatus(), [OrderStatus::RETURNED, OrderStatus::REFUNDED])) {
            return YardiSettings::REVERSAL_PAYMENT_TYPE;
        }

        $type = $yardiSettings->getOrderPaymentType($order);
        if (!$type) {
            throw new \Exception(
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
     * @param PaymentDetail $detail
     */
    public function setDetail(PaymentDetail $detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return PaymentDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }
}
