<?php

namespace RentJeeves\CheckoutBundle\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class StartDate extends Constraint
{
    public $oneTimeUntilValue;

    public $minDate;

    public $messageDateInPast = 'payment.start_date.error.past';

    public $messageDateOutsideRollingWindow = 'payment.start_date.error.outside_rolling_window';

    public $messageDateCutoffTime = 'payment.start_date.error.cutoff.time';

    public $messageEmptyStartDate = 'payment.start_date.error.empty_date';
}
