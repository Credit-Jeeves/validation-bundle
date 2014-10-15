<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Model\Payment as Base;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentRepository")
 * @Assert\Callback(methods={"isEndLaterThanStart"})
 */
class Payment extends Base
{
    public function checkContract()
    {
        $contract = $this->getContract();
        $status = $contract->getStatus();
        if (in_array($status, array(ContractStatus::PENDING, ContractStatus::INVITE))) {
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

    public function getStartDate()
    {
        $date = new DateTime('0000-00-00T00:00:00');
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
        return $this->getStartDate()->format('m/d/Y') . ' ' . $this->getType();
    }

    public function createJob()
    {
        $job = new Job('payment:pay', array('--app=rj'));
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
            if ($now < $this->getStartDate()) {
                $month = $this->getStartMonth();
                $year = $this->getStartYear();
            }
        }
        // modify and setDate do not work together, so we need to take non modified 'now'
        $now = $this->getNow();

        return $now->setDate($year, $month, $day);
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("amountOther")
     * @Serializer\Groups({"payRent"})
     *
     * @return float|null
     */
    public function getOther()
    {
        return ((0 < $this->getTotal())?$this->getTotal() - $this->getAmount() : null);
    }

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
}
