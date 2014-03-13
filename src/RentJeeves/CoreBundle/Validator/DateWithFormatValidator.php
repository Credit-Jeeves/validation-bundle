<?php

namespace RentJeeves\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use JMS\DiExtraBundle\Annotation\Validator;
use \DateTime;
use \Exception;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Validator("date_with_format_validator")
 */
class DateWithFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        try {
            $date = DateTime::createFromFormat($constraint->format, $value);
        } catch (Exception $e) {
            $this->context->addViolation($constraint->message);
        }
    }
}
