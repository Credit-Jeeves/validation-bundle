<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use \DateTime;

class StartDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $now = new DateTime($constraint->oneTimeUntilValue);
        $date = DateTime::createFromFormat('Y-m-d', $value);
        if ($now < $date) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
