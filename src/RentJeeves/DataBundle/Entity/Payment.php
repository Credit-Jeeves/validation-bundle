<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Model\Payment as Base;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use RentJeeves\CheckoutBundle\Constraint\StartDate;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentRepository")
 * @Assert\Callback(methods={"validate"}, groups={"Default", "pay_anything"})
 */
class Payment extends Base
{

    /**
     * Time limit for executing a payment
     *
     * @var int
     */
    const MAXIMUM_RUNTIME_SEC = 600; // 10 minutes

    /**
     *
     * The 'other' parameter is a calculated value from $amount and $total
     *
     * @Assert\Range(
     *      min=0,
     *      minMessage="checkout.error.other.min",
     *      invalidMessage="checkout.error.other.valid"
     * )
     * @Serializer\Groups({"payRent"})
     *
     * @var double
     */
    protected $other = 0.0;

    /**
     * FLAGGED is internal status. It should not be visible for users.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("status")
     * @Serializer\Groups({"payRent"})
     *
     * @return PaymentStatus|string
     */
    public function getDisplayedStatus()
    {
        if (PaymentStatus::FLAGGED === $this->status) {
            return PaymentStatus::ACTIVE;
        }

        return $this->status;
    }

    public function checkContract()
    {
        $contract = $this->getContract();
        $status = $contract->getStatus();
        if (in_array($status, [ContractStatus::PENDING, ContractStatus::INVITE]) &&
            $this->getDepositAccount()->getType() === DepositAccountType::RENT
        ) {
            $contract = $this->getContract()->initiatePaidTo();
            $contract->setStatus(ContractStatus::APPROVED);
            $this->setContract($contract);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentAccountId()
    {
        return $this->paymentAccount->getId();
    }

    public function setStartDate($date = 'now')
    {
        $dateTime = new DateTime($date);
        $this->setDueDate($dateTime->format('j'));
        $this->setStartMonth($dateTime->format('n'));
        $this->setStartYear($dateTime->format('Y'));
    }

    /**
     * @StartDate(
     *    oneTimeUntilValue="21:50",
     *    groups={"api"}
     * )
     */
    public function getStartDate()
    {
        if (!$this->getStartYear() || !$this->getStartMonth() || !$this->getDueDate()) {
            return null;
        }
        $date = new \DateTime('0000-00-00T00:00:00');

        return $date->setDate($this->getStartYear(), $this->getStartMonth(), $this->getDueDate());
    }

    public function setEndDate($date = '+ 9 months')
    {
        $dateTime = new DateTime($date);
        $this->setEndMonth($dateTime->format('m'));
        $this->setEndYear($dateTime->format('Y'));
    }

    public function __toString()
    {
        return ($this->getStartDate() ? $this->getStartDate()->format('m/d/Y') : '') . ' ' . $this->getType();
    }

    public function createJob()
    {
        $job = new Job('payment:pay', array('--app=rj'));
        $job->setMaxRuntime(self::MAXIMUM_RUNTIME_SEC);
        $job->addRelatedEntity($this);

        return $job;
    }

    protected function getNow()
    {
        return new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getNextPaymentDate(DateTime $lastPaymentDate = null)
    {
        // 1. Get start date
        $day = $this->getDueDate();

        // 2. Get now
        $now = $this->getNow();

        // if payment start_date is in future, use it as a next payment date
        if ($now < $this->getStartDate()) {
            $day = $this->getDueDate();
            $month = $this->getStartMonth();
            $year = $this->getStartYear();

            return $now->setDate($year, $month, $day);
        }

        // 3. Get current day, month, year
        $currentDay = $now->format('d');
        $currentMonth = $now->format('m');
        $currentYear = $now->format('Y');

        if ($currentDay > $this->getDueDate()) { // 4. If payment day has already gone, we should take next month
            $now->modify('first day of next month');
            $month = $now->format('n');
            $year = $now->format('Y');
            // 5. If due date is today and payment has been made today, we should move to next month
        } elseif (($currentDay == $this->getDueDate())
            && $lastPaymentDate
            && $lastPaymentDate->format('Ymd') == $now->format('Ymd')
            && $this->type == PaymentType::RECURRING
        ) {
            $now->modify('first day of next month');
            $month = $now->format('m');
            $year = $now->format('Y');
        } else { // 5. If the day is Ok, month and year should be the current ones unless NOW is before START_DATE
            $month = $currentMonth;
            $year = $currentYear;
        }
        // modify and setDate do not work together, so we need to take non modified 'now'
        $now = $this->getNow();

        return $now->setDate($year, $month, $day);
    }

    public function getOther()
    {
        return ($this->total > $this->amount) ? $this->total - $this->amount : 0.0;
    }

    public function setOther($value)
    {
        $this->other = $value;
        $this->calcTotalFromOther();
    }

    public function setAmount($value)
    {
        parent::setAmount($value);
        if ($this->other >= 0) {
            $this->calcTotalFromOther();
        }
    }

    private function calcTotalFromOther()
    {
        if ($this->amount > 0) {
            # $amount set before $other
            $this->total = $this->amount + $this->other;
        } else {
            # $other set before $amount
            $this->total = $this->other;
        }
    }

    /*
     * this maps the API paid_for parameter to the model's paidFor parameter
     */
    public function setPaidForApi($dateText)
    {
        $date = date_parse($dateText);
        if ($date['year'] &&  $date['month']) {
            $day = $date['day'] ? $date['day'] : $this->getDueDate();
            $datePaidFor = new DateTime();
            $datePaidFor->setDate($date['year'], $date['month'], $day);
            parent::setPaidFor($datePaidFor);
        } else {
            parent::setPaidFor(null);
        }
    }

    public function getPaidForApi()
    {
        $paidFor = parent::getPaidFor();

        return ($paidFor) ? $paidFor->format("Y-m") : "";
    }

    /**
     * @param ExecutionContextInterface $validatorContext
     */
    public function isEndLaterThanStart(ExecutionContextInterface $validatorContext)
    {
        if (!$this->getStartYear() || !$this->getStartMonth() || !$this->getDueDate() ||
            !$this->getEndMonth() || !$this->getEndYear()
        ) {
            return;
        }
        $end = new DateTime();
        $end->setTime(0, 0, 0);
        $end->setDate($this->getEndYear(), $this->getEndMonth(), $this->getDueDate());
        if ($end < $this->getStartDate()) {
            $validatorContext->addViolationAt('endMonth', 'contract.error.is_end_later_than_start', array(), null);
        }
    }

    public function validate(ExecutionContextInterface $context)
    {
        $now = $this->getNow();

        if ($this->getStartYear() && $this->getStartYear() < $now->format('Y')) {
            $context->addViolationAt('startYear', "payment.year.error.past");
        }

        if ($this->getEndYear() && $this->getEndYear() < $now->format('Y')) {
            $context->addViolationAt('endYear', "payment.end_year.error.past");
        }

        $group = $this->getContract() ? $this->getContract()->getGroup() : null;
        $payBalanceOnly = $group ? $group->getGroupSettings()->getPayBalanceOnly() : null;

        if (!$this->getPaidFor() && !$payBalanceOnly && $context->getGroup() === 'Default') {
            $context->addViolationAt(null, 'error.contract.paid_for');
        }
         // if month > 12 the method  $end->setDate with this param returned 500
        if ($this->getStartMonth() < 1 || $this->getStartMonth() > 12) {
            return;
        }

        $lastDayInStartMonth = new DateTime("last day of {$this->getStartYear()}-{$this->getStartMonth()}");

        if ($this->getType() == PaymentType::ONE_TIME && $lastDayInStartMonth->format('d') < $this->getDueDate()) {
            $context->addViolationAt(
                'day',
                "payment.month.error.number",
                ['%count%' => $lastDayInStartMonth->format('d')]
            );

            return;
        }

        // if month > 12 the method  $end->setDate with this param returned 500
        if ($this->getEndMonth() && ($this->getEndMonth() < 1 || $this->getEndMonth() > 12)) {
            return;
        }

        if ($this->getEndYear() && $this->getEndMonth()) {
            $lastDayInEndMonth = new DateTime("last day of {$this->getEndYear()}-{$this->getEndMonth()}");
            if ($this->getType() == PaymentType::ONE_TIME && $lastDayInEndMonth->format('d') < $this->getDueDate()) {
                $context->addViolationAt(
                    'day',
                    "payment.month.error.number",
                    ['%count%' => $lastDayInEndMonth->format('d')]
                );

                return;
            }
        }

        $this->isEndLaterThanStart($context);
    }

    public function setActive()
    {
        $this->setStatus(PaymentStatus::ACTIVE);

        return $this;
    }

    public function setClosed($caller, $reason)
    {
        if (!is_object($caller)) {
            throw new Exception('Can not set the close reason. Caller name is not an object');
        }
        if (empty($reason)) {
            throw new Exception('Payment close reason is missing');
        }

        $this->setStatus(PaymentStatus::CLOSE);
        $details = [
            "Class: " . get_class($caller),
            "Reason: " . $reason
        ];
        $this->setCloseDetails($details);

        return $this;
    }

    public function isRecurring()
    {
        return PaymentType::RECURRING == $this->getType();
    }
}
