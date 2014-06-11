<?php
namespace RentJeeves\CoreBundle;

use DateTimeZone;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class DateTime extends \DateTime
{
    const MONTH_YEAR_PATTERN = '/( ?[-+]?[\d\w]+ (month|year)s?){1,2}/i';

    /**
     * @todo fix '2014-02-31' result must be '2014-02-28'
     * @param string $time
     * @param DateTimeZone $timezone
     */
    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        $matches = array();
        preg_match(
            static::MONTH_YEAR_PATTERN,
            $time,
            $matches
        );

        if (!empty($matches[0])) {
            $time = preg_replace(static::MONTH_YEAR_PATTERN, '', $time);
        }

        if (!trim($time)) {
            $time = 'now';
        }
        parent::__construct($time, $timezone);

        if (!empty($matches[0])) {
            $this->modify($matches[0]);
        }
    }

    /**
     * @param int|null $year
     * @param int|null $month
     * @param int|null $day
     *
     * @return $this
     */
    public function setDate($year, $month, $day)
    {
        if (null == $year) {
            $year = $this->format('Y');
        }
        if (null == $month) {
            $month = $this->format('n');
        }
        if (null == $day) {
            $day = $this->format('j');
        }
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = $day > $daysInMonth ? $daysInMonth : $day;
        $return = parent::setDate((int)$year, (int)$month, (int)$day);
        return $return;
    }

    public function modifyCallback($matches)
    {
        if (empty($matches[0])) {
            return;
        }
        $orDay = $this->format('j');
        $this->setDate(null, null, 1);
        if (!parent::modify($matches[0])) {
            return;
        }
        $this->setDate($this->format('Y'), $this->format('n'), $orDay);

        return;
    }

    protected function modifyMonth($modify)
    {
        return preg_replace_callback(
            static::MONTH_YEAR_PATTERN,
            array($this, 'modifyCallback'),
            $modify
        );
    }


    /**
     * @inheritdoc
     */
    public function modify($modify)
    {
        $modify = $this->modifyMonth($modify);
        if ($modify = trim($modify)) {
            return parent::modify($modify);
        }
        return $this;
    }
}
