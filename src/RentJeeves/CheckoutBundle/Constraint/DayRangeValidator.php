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

        $day = (int) $date->format('j');
        if ($constraint->openDay <= $constraint->closeDay &&
            $constraint->closeDay >= $day && $constraint->openDay <= $day
        ) {
            return;
        }

        if ($constraint->openDay >= $constraint->closeDay &&
            $constraint->openDay <= $day
        ) {
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
}
