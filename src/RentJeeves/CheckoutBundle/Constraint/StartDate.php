<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StartDate extends Constraint
{
    public $oneTimeUntilValue;

    public $message = 'Please check your dates. It appears you have set up a recurring payment to start in the past.
                       You must start it today or in the future.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
