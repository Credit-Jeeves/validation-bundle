<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class DayRange extends Constraint
{
    public $message = 'payment_form.start_date.error_range';

    public $openDay;

    public $closeDay;
}
