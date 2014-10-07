<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class StartDate extends Constraint
{
    public $oneTimeUntilValue;

    public $messageDateInPast = 'payment.start_date.error.past';

    public $messageDateCutoffTime = 'payment.start_date.error.cutoff.time';

    public $messageEmptyStartDate = 'payment.start_date.error.empty_date';
}
