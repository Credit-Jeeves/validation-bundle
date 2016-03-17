<?php

namespace RentJeeves\DataBundle\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PaymentDate extends Constraint
{
    public $oneTimeDebitUntilValue;

    public $messageStartYearInPast = 'payment.year.error.past';

    public $messageEndYearInPast = 'payment.end_year.error.past';

    public $messageDebitDateCutoffTime = 'payment.start_date.error.debit.cutoff.time';

    public $messageIncorrectContractPaidFor = 'error.contract.paid_for';

    public $messageIncorrectNumberMonth = 'payment.month.error.number';

    public $messageEndLaterThanStart = 'contract.error.is_end_later_than_start';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'payment_date.validator';
    }
}
