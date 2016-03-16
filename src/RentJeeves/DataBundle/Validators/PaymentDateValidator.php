<?php

namespace RentJeeves\DataBundle\Validators;

use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\Constraint\StartDateValidator;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @DI\Validator("payment_date.validator")
 */
class PaymentDateValidator extends ConstraintValidator
{
    /**
     * @var string
     */
    public $oneTimeDebitUntilValue;

    /**
     * @param string $oneTimeDebitUntilValue
     * @DI\InjectParams({
     *     "oneTimeDebitUntilValue" = @DI\Inject("%payment_debit_one_time_until_value%")
     * })
     */
    public function __construct($oneTimeDebitUntilValue)
    {
        $this->oneTimeDebitUntilValue = $oneTimeDebitUntilValue;
    }

    /**
     * @param Payment $object
     * @param Constraint|PaymentDate $constraint
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$object instanceof Payment) {
            return;
        }

        $now = new \DateTime();

        if ($object->getStartYear() && $object->getStartYear() < $now->format('Y')) {
            $this->context->addViolationAt('startYear', $constraint->messageStartYearInPast);
        }

        if ($object->getEndYear() && $object->getEndYear() < $now->format('Y')) {
            $this->context->addViolationAt('endYear', $constraint->messageEndYearInPast);
        }

        $oneTimeDebitUntilValue = $constraint->oneTimeDebitUntilValue ?: $this->oneTimeDebitUntilValue;
        if ($this->context->getGroup() === 'last_step' &&
            $object->getPaymentAccount() &&
            $object->getPaymentAccount()->getType() === PaymentAccountType::DEBIT_CARD &&
            $object->getStartDate() &&
            $object->getStartDate()->format('Y-m-d') === $now->format('Y-m-d') &&
            StartDateValidator::isPastCutoffTime($object->getStartDate(), $oneTimeDebitUntilValue)
        ) {
            $this->context->addViolationAt('start_date', $constraint->messageDebitDateCutoffTime);
        }

        $group = $object->getContract() ? $object->getContract()->getGroup() : null;
        $payBalanceOnly = $group ? $group->getGroupSettings()->getPayBalanceOnly() : null;

        if (!$object->getPaidFor() && !$payBalanceOnly && $this->context->getGroup() === 'Default') {
            $this->context->addViolationAt(null, $constraint->messageIncorrectContractPaidFor);
        }

        if ($object->getType() === PaymentType::ONE_TIME && $object->getStartDate()) {
            $this->checkCountDaysOfMonth(
                $constraint,
                $object->getStartYear(),
                $object->getStartMonth(),
                $object->getDueDate()
            );

            if ($object->getEndMonth() && $object->getEndYear()) {
                $this->checkCountDaysOfMonth(
                    $constraint,
                    $object->getEndYear(),
                    $object->getEndMonth(),
                    $object->getDueDate()
                );

                $endDate = (new \DateTime())
                    ->setTime(0, 0, 0)
                    ->setDate($object->getEndYear(), $object->getEndMonth(), $object->getDueDate());

                if ($endDate < $object->getStartDate()) {
                    $this->context->addViolationAt('endMonth', $constraint->messageEndLaterThanStart);
                }
            }
        }

    }

    /**
     * @param PaymentDate $constraint
     * @param int $year
     * @param int $month
     * @param int $dueDate
     */
    protected function checkCountDaysOfMonth(PaymentDate $constraint, $year, $month, $dueDate)
    {
        // if month > 12 the method setDate with this param returned 500
        if ($month < 1 || $month > 12) {
            return;
        }

        $lastDayInMonth = new \DateTime(sprintf('last day of %s-%s', $year, $month));

        if ($lastDayInMonth->format('d') < $dueDate) {
            $this->context->addViolationAt(
                'day',
                $constraint->messageIncorrectNumberMonth,
                ['%count%' => $lastDayInMonth->format('d')]
            );
        }
    }
}
