<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use \DateTime;

class DayRangeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);

        if (!$date) {
            return;
        }

        if (self::inRange($date, $constraint->openDay, $constraint->closeDay)) {
            return;
        }

        return $this->context->addViolation('payment_form.start_date.error_range', [
            '%OPEN_DAY%'      => $constraint->openDay,
            '%CLOSE_DAY%'     => $constraint->closeDay
        ]);
    }

    public static function inRange(DateTime $date, $openDay, $closeDay)
    {
        $day = (int) $date->format('j');
        if ($openDay <= $closeDay &&
            $closeDay >= $day && $openDay <= $day
        ) {
            return true;
        }

        if ($openDay >= $closeDay &&
            $openDay <= $day
        ) {
            return true;
        }

        if ($openDay >= $closeDay &&
            $closeDay >= $day
        ) {
            return true;
        }

        return false;
    }
}
