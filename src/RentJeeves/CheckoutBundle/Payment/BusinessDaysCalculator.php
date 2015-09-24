<?php

namespace RentJeeves\CheckoutBundle\Payment;

class BusinessDaysCalculator
{
    /**
     * @param \DateTime $currentDate
     *
     * @return \DateTime
     */
    public static function getNextBusinessDate(\DateTime $currentDate)
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

    /**
     * @param \DateTime $startDate
     * @param integer  $targetShift
     *
     * @return \DateTime
     */
    public static function getBusinessDate(\DateTime $startDate, $targetShift)
    {
        $businessDate = $startDate;
        $shiftedDays = 0;

        while ($shiftedDays < $targetShift) {
            $businessDate = self::getNextBusinessDate($businessDate);
            $shiftedDays++;
        }

        return $businessDate;
    }
}
