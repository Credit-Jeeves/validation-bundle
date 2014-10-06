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

        $message = $constraint->translator->trans(
            'payment_form.start_date.error_range',
            array(
                '%OPEN_DAY%'      => $constraint->openDay,
                '%CLOSE_DAY%'     => $constraint->closeDay
            )
        );

        return $this->context->addViolation($message);
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
