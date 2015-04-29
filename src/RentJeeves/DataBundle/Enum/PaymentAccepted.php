<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentAccepted extends Enum
{
    const ANY = 0;

    const DO_NOT_ACCEPT = 1;

    const CASH_EQUIVALENT = 2;

    /**
     * @return array
     */
    public static function getDeniedValues()
    {
        return [self::DO_NOT_ACCEPT, self::CASH_EQUIVALENT];
    }

    /**
     * Returns an array of values with currentValue as a first value
     *
     * @param $currentState
     * @return array
     */
    public static function getValues($currentValue)
    {
        $currentValues = [$currentValue => $currentValue];
        $otherValues = array_diff(
            PaymentAccepted::all(),
            $currentValues
        );
        $result = [];
        foreach (($currentValues + $otherValues) as $key => $value) {
            $result[$value]= $value;
        }

        return $result;
    }
}
