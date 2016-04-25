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
    public static function getDepositDate(\DateTime $startDate, $targetShift)
    {
        $businessDate = $startDate;
        $shiftedDays = 0;

        while ($shiftedDays < $targetShift) {
            $businessDate = self::getNextBusinessDate($businessDate);
            $shiftedDays++;
        }

        return $businessDate;
    }

    /**
     * Calculates next deposit date with the following rules:
     *  - if startDate is Friday - then deposit date Monday
     *  - if startDate is Saturday - then deposit date Tuesday
     *  - if startDate is Sunday - then deposit date Tuesday
     * All other days of week are deposited next day.
     *
     * @param \DateTime $startDate
     * @return \DateTime
     */
    public static function getNextDepositDate(\DateTime $startDate)
    {
        $businessDate = self::getBusinessDate($startDate);

        return self::getNextBusinessDate($businessDate);
    }

    /**
     * Calculates the date when payment processed.
     * Shifts weekend days to Monday.
     * All other days remain without changes.
     *
     * @param \DateTime $date
     * @return \DateTime
     */
    public static function getBusinessDate(\DateTime $date)
    {
        $businessDate = clone $date;

        switch ($date->format('N')) {
            case '7': $businessDate->modify('+1 days');
                break;
            case '6': $businessDate->modify('+2 days');
                break;
        }

        return $businessDate;
    }
}
