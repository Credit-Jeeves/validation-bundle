<?php

namespace RentJeeves\ComponentBundle\Helper;

class DateCheckerHelper
{
    /**
     *
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return boolean
     */
    public static function checkDateFallsBetweenDates(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        $today = new \DateTime();
        $todayStr = (int) $today->format('Ymd');
        //both parameter provider
        if (($startDate instanceof \DateTime && $endDate instanceof \DateTime) &&
            (int) $startDate->format('Ymd') <= $todayStr && (int) $endDate->format('Ymd') >= $todayStr
        ) {
            return true;
        }

        //only startDate parameter provider
        if (($startDate instanceof \DateTime && !($endDate instanceof \DateTime)) &&
            (int) $startDate->format('Ymd') <= $todayStr
        ) {
            return true;
        }

        //only endDate parameter provider
        if ((!($startDate instanceof \DateTime) && $endDate instanceof \DateTime) &&
            (int) $endDate->format('Ymd') >= $todayStr
        ) {
            return true;
        }

        if (empty($startDate) && empty($endDate)) {
            return true;
        }

        return false;
    }
}
