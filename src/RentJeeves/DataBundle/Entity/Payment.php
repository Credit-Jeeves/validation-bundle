<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Model\Payment as Base;
use RentJeeves\DataBundle\Enum\ContractStatus;
use DateTime;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentRepository")
 * @ORM\HasLifecycleCallbacks()
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
        $this->setDueDate($dateTime->format('d'));
        $this->setStartMonth($dateTime->format('m'));
        $this->setStartYear($dateTime->format('Y'));
    }

    public function getStartDate()
    {
        return new DateTime($this->getDueDate() . '-' . $this->getStartMonth() . '-' . $this->getStartYear());
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

    /**
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $e)
    {
        $em = $e->getEntityManager();
//        $em->detach($this);
        $this->setStatus(PaymentStatus::CLOSE);
        $em->persist($this);
        $em->flush($this);
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
            $now->setDate($now->format( 'Y' ), $now->format( 'm' ), 1);
            $now->modify('+1 month');
            $month = $now->format('m');
            $year = $now->format('Y');
            // 5. If due date is today and payment has been made today, we should move to next month
        } elseif (($currentDay == $this->getDueDate())
            && $lastPaymentDate
            && $lastPaymentDate->format('Ymd') == $now->format('Ymd')
        ) {
            $now->setDate($now->format( 'Y' ), $now->format( 'm' ), 1);
            $now->modify('+1 month');
            $month = $now->format('m');
            $year = $now->format('Y');
        } else { // 5. If the day is Ok, month and year should be the current ones
            $month = $currentMonth;
            $year = $currentYear;
        }

        $daysInMont = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if ($day > $daysInMont) {
            $day = $daysInMont;
        }

        return new DateTime(implode('-', array($year, $month, $day)));
    }
}
