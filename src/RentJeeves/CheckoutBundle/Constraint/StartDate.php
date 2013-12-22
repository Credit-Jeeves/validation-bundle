<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class StartDate extends Constraint
{
    public $oneTimeUntilValue;

    public $message = 'payment.start_date.error';
}
