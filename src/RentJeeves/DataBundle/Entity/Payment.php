<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\Payment as Base;
use RentJeeves\DataBundle\Enum\ContractStatus;
use DateTime;

/**
 * @ORM\Table(name="rj_payment")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\PaymentRepository")
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
}
