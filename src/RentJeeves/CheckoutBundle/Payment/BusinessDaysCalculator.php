<?php

namespace RentJeeves\CheckoutBundle\Payment;

use JMS\DiExtraBundle\Annotation as DI;
use DateTime;

/**
 * @DI\Service("business_days_calculator")
 */
class BusinessDaysCalculator
{
    public function getNextBusinessDate(DateTime $currentDate)
    {
        switch ($currentDate->format('N')) {
            case '6': $nextBusinessDate = $currentDate->modify('+2 days');
                break;
            case '5': $nextBusinessDate = $currentDate->modify('+3 days');
                break;
            default: $nextBusinessDate = $currentDate->modify('+1 day');
        }

        return $nextBusinessDate;
    }
}
