<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Enum;

use CreditJeeves\CoreBundle\Enum;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;

class ReversalReasonEnum extends Enum
{
    const REASON_CHARGE_BACK = 'ChargeBack';
    const REASON_NSF = 'NSF';
    const REASON_REVERSE = 'Reverse';

    /**
     * @param Order $order
     *
     * @return string
     */
    public static function getReasonByOrder(Order $order)
    {
        if ($order->getStatus() === OrderStatus::RETURNED && $order->getType() === OrderType::HEARTLAND_BANK) {
            return self::REASON_NSF;
        } elseif ($order->getStatus() === OrderStatus::RETURNED && $order->getType() === OrderType::HEARTLAND_CARD) {
            return self::REASON_CHARGE_BACK;
        } else {
            return self::REASON_REVERSE;
        }
    }
}
