<?php

namespace RentJeeves\CheckoutBundle\Payment;

use JMS\DiExtraBundle\Annotation as DI;
use DateTime;

/**
 * @DI\Service("business_days_calculator")
 */
class BusinessDaysCalculator
{
    protected $ccBusinessDays;
    protected $achBusinessDays;

    /**
     * @DI\InjectParams({
     *     "ccBusinessDays" = @DI\Inject("%business_days_cc%"),
     *     "achBusinessDays" = @DI\Inject("%business_days_ach%"),
     * })
     */
    public function __construct($ccBusinessDays, $achBusinessDays)
    {
        $this->ccBusinessDays = $ccBusinessDays;
        $this->achBusinessDays = $achBusinessDays;
    }

    public function getBusinessDate(DateTime $startDate, $targetShift)
    {
        $businessDate = $startDate;
        $shiftedDays = 0;
        $targetShift += 1;

        while ($shiftedDays <= $targetShift) {
            $currentDayOfWeek = $businessDate->format('N');

            if ($currentDayOfWeek <= 5) {
                $shiftedDays++;
            }

            if ($shiftedDays == $targetShift) {
                break;
            }

            $businessDate = $businessDate->modify('+1 day');
        }

        return $businessDate;
    }

    public function getCreditCardBusinessDate(DateTime $startDate)
    {
        return $this->getBusinessDate($startDate, $this->ccBusinessDays);
    }

    public function getACHBusinessDate(DateTime $startDate)
    {
        return $this->getBusinessDate($startDate, $this->achBusinessDays);
    }

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
