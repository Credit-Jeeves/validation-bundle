<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class StartDate extends Constraint
{
    public $oneTimeUntilValue;

    public $message = 'payment.start_date.error';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
