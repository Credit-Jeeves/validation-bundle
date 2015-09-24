<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class StartDateValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $nowDateTime = new \DateTime();
        $dateValidation = ($value instanceof \DateTime) ? $value : \DateTime::createFromFormat('Y-m-d', $value);

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
            if (self::isPastCutoffTime($dateValidation, $constraint->oneTimeUntilValue)) {
                return $this->context->addViolation($constraint->messageDateCutoffTime);
            }

            return;
        }

        return $this->context->addViolation($constraint->messageDateInPast);
    }

    /**
     * @param \DateTime $dateValidation
     * @param string $oneTimeUntilValue
     * @return bool
     */
    public static function isPastCutoffTime(\DateTime $dateValidation, $oneTimeUntilValue)
    {
        $nowDateTime = new \DateTime();

        if ($dateValidation->format('Y-m-d') > $nowDateTime->format('Y-m-d')) {
            return false;
        }

        if ($dateValidation->format('Y-m-d') === $nowDateTime->format('Y-m-d')) {
            $nowDateTimeWithCrontabTimeExecution = new \DateTime($oneTimeUntilValue);
            $timeExecution = (int) $nowDateTimeWithCrontabTimeExecution->format('U');
            $timeValidation = (int) $dateValidation->format('U');

            if ($timeExecution > $timeValidation) {
                return false;
            }
        }

        return true;
    }
}
