<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;
use CreditJeeves\DataBundle\Enum\OrderType;

class PaymentProcessor extends Enum
{
    const HEARTLAND = 'heartland';

    const ACI = 'aci';

    public static function mapByOrderType($orderType)
    {
        switch ($orderType) {
            case OrderType::HEARTLAND_BANK:
            case OrderType::HEARTLAND_CARD:
                return self::HEARTLAND;
            default:
                return null;
        }
    }
}
