<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use DateTime;

class ExperianReportRecord
{
    /**
     * @Serializer\Exclude
     */
    protected $contract;

    /** @Serializer\Accessor(getter="getTenantUniqueIdentifier") */
    protected $tenantUniqueIdentifier;
    /** @Serializer\Accessor(getter="getTenantFirstName") */
    protected $tenantFirstName;
    /** @Serializer\Accessor(getter="getTenantMiddleName") */
    protected $tenantMiddleName;
    /** @Serializer\Accessor(getter="getTenantLastName") */
    protected $tenantLastName;
    /** @Serializer\Accessor(getter="getTenantSSN") */
    protected $tenantSSN;
    /** @Serializer\Accessor(getter="getTenantDOB") */
    protected $tenantDOB;
    /** @Serializer\Accessor(getter="getTenantAddressUniqueIdentifier") */
    protected $tenantAddressUniqueIdentifier;
    /** @Serializer\Accessor(getter="getTenantAddress1") */
    protected $tenantAddress1;
    /** @Serializer\Accessor(getter="getTenantAddress2") */
    protected $tenantAddress2;
    /** @Serializer\Accessor(getter="getTenantCity") */
    protected $tenantCity;
    /** @Serializer\Accessor(getter="getTenantState") */
    protected $tenantState;
    /** @Serializer\Accessor(getter="getTenantZip") */
    protected $tenantZip;
    /** @Serializer\Accessor(getter="getLeaseUniqueIdentifier") */
    protected $leaseUniqueIdentifier;
    /** @Serializer\Accessor(getter="getEnrollmentDate") */
    protected $enrollmentDate;
    /** @Serializer\Accessor(getter="getLeaseEndDate") */
    protected $leaseEndDate;
    /** @Serializer\Accessor(getter="getMoveOutBalanceOwed") */
    protected $moveOutBalanceOwed;
    /** @Serializer\Accessor(getter="getLeaseMoveOutDate") */
    protected $leaseMoveOutDate;
    /** @Serializer\Accessor(getter="getMonthToMonthLease") */
    protected $monthToMonthLease;
    /** @Serializer\Accessor(getter="getSubsidizedLease") */
    protected $subsidizedLease;
    /** @Serializer\Accessor(getter="getPaymentUniqueIdentifier") */
    protected $paymentUniqueIdentifier;
    /** @Serializer\Accessor(getter="getMonthlyRentAmountDue") */
    protected $monthlyRentAmountDue;
    /** @Serializer\Accessor(getter="getDueDate") */
    protected $dueDate;
    /** @Serializer\Accessor(getter="getAmountPaid") */
    protected $amountPaid;
    /** @Serializer\Accessor(getter="getDatePaid") */
    protected $datePaid;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function getDatePaid()
    {
        return '20140228';
    }

    public function getDueDate()
    {
        return '20140301';
    }

    public function getEnrollmentDate()
    {
        return $this->contract->getStartAt();
    }

    public function getLeaseEndDate()
    {
        if ($finishAt = $this->contract->getFinishAt()) {
            return $finishAt->format('Ymd');
        }

        return null;
    }

    public function getLeaseMoveOutDate()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            return $this->contract->getFinishAt()->format('Ymd');
        }

        return null;
    }

    public function getLeaseUniqueIdentifier()
    {
        return $this->contract->getId();
    }

    public function getMonthToMonthLease()
    {
        if ($this->contract->getFinishAt() == null) {
            return 1;
        }

        return null;
    }

    public function getMonthlyRentAmountDue()
    {
        return $this->contract->getRent();
    }

    public function getMoveOutBalanceOwed()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            return $this->contract->getUncollectedBalance();
        }

        return 0;
    }

    public function getPaymentUniqueIdentifier()
    {
        return 111;
    }

    public function getSubsidizedLease()
    {
        return null;
    }

    public function getTenantAddress1()
    {
        $property = $this->contract->getProperty();
        $address = sprintf('%s %s', $property->getNumber(), $property->getStreet());
        if ($unit = $this->contract->getUnit()) {
           return sprintf('%s %s', $address, $unit->getName());
        }

        return $address;
    }

    public function getTenantAddress2()
    {
        return null;
    }

    public function getTenantAddressUniqueIdentifier()
    {
        if ($unit = $this->contract->getUnit()) {
            return $unit->getId();
        }

        return $this->contract->getProperty()->getId();
    }

    public function getTenantCity()
    {
        return $this->contract->getProperty()->getCity();
    }

    public function getTenantDOB()
    {
        return $this->contract->getTenant()->getDateOfBirth()->format('mdY');
    }

    public function getTenantFirstName()
    {
        return $this->contract->getTenant()->getFirstName();
    }

    public function getTenantLastName()
    {
        return $this->contract->getTenant()->getLastName();
    }

    public function getTenantMiddleName()
    {
        return $this->contract->getTenant()->getMiddleInitial();
    }

    public function getTenantSSN()
    {
        return $this->contract->getTenant()->getSsn();
    }

    public function getTenantState()
    {
        return $this->contract->getProperty()->getArea();
    }

    public function getTenantUniqueIdentifier()
    {
        return $this->contract->getTenant()->getId();
    }

    public function getTenantZip()
    {
        return $this->contract->getProperty()->getZip();
    }

    public function getAmountPaid()
    {
        return 555;
    }
}
