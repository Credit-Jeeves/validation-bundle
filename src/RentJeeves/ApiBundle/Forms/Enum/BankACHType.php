<?php

namespace RentJeeves\ApiBundle\Forms\Enum;

use CreditJeeves\CoreBundle\Enum;
use Payum\Heartland\Soap\Base\ACHDepositType;

class BankACHType extends Enum
{
    const CHECKING = 'checking';

    const SAVINGS = 'savings';

    const BUSINESS_CHECKING = 'business checking';

    public static function getMapValue($type)
    {
        if ($type != self::BUSINESS_CHECKING) {
            return ucfirst($type);
        }

        return ACHDepositType::UNASSIGNED;
    }
}
