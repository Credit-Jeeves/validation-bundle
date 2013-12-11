<?php
namespace RentJeeves\CoreBundle\Traits;

trait DateCommon
{
    public function getDueDays($forward = 0)
    {
        $date = new \DateTime();
        if ($forward > 0) {
            $date = new \DateTime('+'.$forward.' days');
        }
        if ($forward < 0) {
            $date = new \DateTime('-1'.$forward.' days');
        }
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
                default:
                    return array($day);
                    break;
            }
        } else {
            return array($day);
        }
    }

    public function getDiffDays($date, $now = null)
    {
        if (empty($now)) {
            $now = new \DateTime();
        }
        return $interval = $date->diff($now)->format('%r%a');
    }
}
