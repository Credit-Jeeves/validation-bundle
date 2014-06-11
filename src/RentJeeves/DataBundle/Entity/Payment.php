<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Model\Payment as Base;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\DateTime;

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

    /**
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
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
            $now->modify('first day of next month');
            $month = $now->format('m');
            $year = $now->format('Y');
            // 5. If due date is today and payment has been made today, we should move to next month
        } elseif (($currentDay == $this->getDueDate())
            && $lastPaymentDate
            && $lastPaymentDate->format('Ymd') == $now->format('Ymd')
        ) {
            $now->modify('first day of next month');
            $month = $now->format('m');
            $year = $now->format('Y');
        } else { // 5. If the day is Ok, month and year should be the current ones
            $month = $currentMonth;
            $year = $currentYear;
        }
        return $now->setDate($year, $month, $day);
    }
}
