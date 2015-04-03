<?php

namespace RentJeeves\ApiBundle\Forms\Enum;

use CreditJeeves\CoreBundle\Enum;
use RentJeeves\CheckoutBundle\Form\Enum\ACHDepositType as BaseACHDepositType;

class ACHDepositType extends Enum
{
    const CHECKING = 'checking';

    const SAVINGS = 'savings';

    const BUSINESS_CHECKING = 'business checking';

    public static function getMapValue($type)
    {
        if ($type != self::BUSINESS_CHECKING) {
            return ucfirst($type);
        }

        return BaseACHDepositType::BUSINESS_CHECKING;
    }
}
