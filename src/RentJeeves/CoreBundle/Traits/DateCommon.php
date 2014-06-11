<?php
namespace RentJeeves\CoreBundle\Traits;

use RentJeeves\CoreBundle\DateTime;

trait DateCommon
{
    public function getDueDays($shift = 0, \DateTime $date = null)
    {
        if (null === $date) {
            $date = new DateTime();
        }
        $date->modify($shift.' days');
        $total = $date->format('t');
        $day = $date->format('j');
        if ($day > 27 & $day >= $total) {
            switch ($total) {
                case 28:
                    return array(28, 29, 30, 31);
                    break;
                case 29:
                    return array(29, 30, 31);
                    break;
                case 30:
                    return array(30, 31);
                    break;
            }
        }
        return array($day);
    }

    public function getDiffDays(\DateTime $date, \DateTime $now = null)
    {
        if (null === $now) {
            $now = new DateTime();
        }
        return $date->diff($now)->format('%r%a');
    }
}
