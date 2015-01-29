<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentAccepted extends Enum
{
    const ANY = 0;

    const DO_NOT_ACCEPT = 1;

    const CASH_EQUIVALENT = 2;

    public static function getDeniedValues()
    {
        return array(
            self::DO_NOT_ACCEPT, self::CASH_EQUIVALENT
        );
    }
}
