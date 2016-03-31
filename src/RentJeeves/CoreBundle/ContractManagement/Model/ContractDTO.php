<?php

namespace RentJeeves\CoreBundle\ContractManagement\Model;

class ContractDTO
{
    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $email;

    /** @var string */
    protected $search;

    /** @var string */
    protected $status;

    /** @var string */
    protected $paymentAccepted;

    /** @var boolean */
    protected $paymentAllowed;

    /** @var float */
    protected $rent;

    /** @var float */
    protected $uncollectedBalance;

    /** @var float */
    protected $integratedBalance;

    /** @var int */
    protected $dueDate;

    /** @var string */
    protected $startAt;

    /** @var string */
    protected $finishAt;

    /** @var string */
    protected $externalResidentId;

    /** @var string */
    protected $externalLeaseId;

    /**
     * @return int
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param int $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getExternalLeaseId()
    {
        return $this->externalLeaseId;
    }

    /**
     * @param string $externalLeaseId
     */
    public function setExternalLeaseId($externalLeaseId)
    {
        $this->externalLeaseId = $externalLeaseId;
    }

    /**
     * @return string
     */
    public function getExternalResidentId()
    {
        return $this->externalResidentId;
    }

    /**
     * @param string $externalResidentId
     */
    public function setExternalResidentId($externalResidentId)
    {
        $this->externalResidentId = $externalResidentId;
    }

    /**
     * @return string
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * @param string $finishAt
     */
    public function setFinishAt($finishAt)
    {
        $this->finishAt = $finishAt;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return float
     */
    public function getIntegratedBalance()
    {
        return $this->integratedBalance;
    }

    /**
     * @param float $integratedBalance
     */
    public function setIntegratedBalance($integratedBalance)
    {
        $this->integratedBalance = $integratedBalance;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param string $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

    /**
     * @return boolean
     */
    public function isPaymentAllowed()
    {
        return $this->paymentAllowed;
    }

    /**
     * @param boolean $paymentAllowed
     */
    public function setPaymentAllowed($paymentAllowed)
    {
        $this->paymentAllowed = $paymentAllowed;
    }

    /**
     * @return float
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * @param float $rent
     */
    public function setRent($rent)
    {
        $this->rent = $rent;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * @return string
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param string $startAt
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return float
     */
    public function getUncollectedBalance()
    {
        return $this->uncollectedBalance;
    }

    /**
     * @param float $uncollectedBalance
     */
    public function setUncollectedBalance($uncollectedBalance)
    {
        $this->uncollectedBalance = $uncollectedBalance;
    }
}
