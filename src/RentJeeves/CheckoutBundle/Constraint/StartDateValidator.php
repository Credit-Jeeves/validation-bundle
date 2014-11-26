<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use \DateTime;

class StartDateValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $nowDateTime = new DateTime();
        $dateValidation = ($value instanceof DateTime) ? $value : DateTime::createFromFormat('Y-m-d', $value);

        if (!$dateValidation) {
            return $this->context->addViolation($constraint->messageEmptyStartDate);
        }

        if ($dateValidation > $nowDateTime) {
            return;
        }

        /**
         * If it's today, need check time
         */
        if ($dateValidation->format('Y-m-d') === $nowDateTime->format('Y-m-d')) {
            $nowDateTimeWithCrontabTimeExecution = new DateTime($constraint->oneTimeUntilValue);
            $timeExecution = (int) $nowDateTimeWithCrontabTimeExecution->format('Hmi');
            $timeValidation = (int) $dateValidation->format('Hmi');

            if ($timeExecution > $timeValidation) {
                return;
            }

            return $this->context->addViolation($constraint->messageDateCutoffTime);
        }

        return $this->context->addViolation($constraint->messageDateInPast);
    }
}
