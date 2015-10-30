<?php

namespace RentJeeves\CoreBundle\Report\Experian;

use CreditJeeves\DataBundle\Entity\Operation;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\Exception\InvalidContractException;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;

class ExperianReportRecord
{
    const EXPERIAN_REPORT_DATE_FORMAT = 'Ymd';

    /**
     * @Serializer\Exclude
     */
    protected $contract;

    /**
     * @Serializer\Exclude
     */
    protected $operation;

    /**
     * @Serializer\Accessor(getter="getTenantUniqueIdentifier")
     * @Serializer\SerializedName("Tenant Unique Identifier")
     */
    protected $tenantUniqueIdentifier;
    /**
     * @Serializer\Accessor(getter="getTenantFirstName")
     * @Serializer\SerializedName("Tenant First Name")
     */
    protected $tenantFirstName;
    /**
     * @Serializer\Accessor(getter="getTenantMiddleName")
     * @Serializer\SerializedName("Tenant Middle Name")
     */
    protected $tenantMiddleName;
    /**
     * @Serializer\Accessor(getter="getTenantLastName")
     * @Serializer\SerializedName("Tenant Last Name")
     */
    protected $tenantLastName;
    /**
     * @Serializer\Accessor(getter="getTenantSSN")
     * @Serializer\SerializedName("Tenant SSN")
     */
    protected $tenantSSN;
    /**
     * @Serializer\Accessor(getter="getTenantDOB")
     * @Serializer\SerializedName("Tenant DOB")
     */
    protected $tenantDOB;
    /**
     * @Serializer\Accessor(getter="getTenantAddressUniqueIdentifier")
     * @Serializer\SerializedName("Tenant Address Unique Identifier")
     */
    protected $tenantAddressUniqueIdentifier;
    /**
     * @Serializer\Accessor(getter="getTenantAddress1")
     * @Serializer\SerializedName("Tenant Address 1")
     */
    protected $tenantAddress1;
    /**
     * @Serializer\Accessor(getter="getTenantAddress2")
     * @Serializer\SerializedName("Tenant Address 2")
     */
    protected $tenantAddress2;
    /**
     * @Serializer\Accessor(getter="getTenantCity")
     * @Serializer\SerializedName("Tenant City")
     */
    protected $tenantCity;
    /**
     * @Serializer\Accessor(getter="getTenantState")
     * @Serializer\SerializedName("Tenant State")
     */
    protected $tenantState;
    /**
     * @Serializer\Accessor(getter="getTenantZip")
     * @Serializer\SerializedName("Tenant Zip")
     */
    protected $tenantZip;
    /**
     * @Serializer\Accessor(getter="getLeaseUniqueIdentifier")
     * @Serializer\SerializedName("Lease Unique Identifier")
     */
    protected $leaseUniqueIdentifier;
    /**
     * @Serializer\Accessor(getter="getEnrollmentDate")
     * @Serializer\SerializedName("Enrollment Date")
     */
    protected $enrollmentDate;
    /**
     * @Serializer\Accessor(getter="getLeaseEndDate")
     * @Serializer\SerializedName("Lease End Date")
     */
    protected $leaseEndDate;
    /**
     * @Serializer\Accessor(getter="getMoveOutBalanceOwed")
     * @Serializer\SerializedName("Move-Out Balance Owed")
     */
    protected $moveOutBalanceOwed;
    /**
     * @Serializer\Accessor(getter="getLeaseMoveOutDate")
     * @Serializer\SerializedName("Lease Move-Out Date")
     */
    protected $leaseMoveOutDate;
    /**
     * @Serializer\Accessor(getter="getMonthToMonthLease")
     * @Serializer\SerializedName("Month-to-month Lease")
     */
    protected $monthToMonthLease;
    /**
     * @Serializer\Accessor(getter="getSubsidizedLease")
     * @Serializer\SerializedName("Subsidized Lease")
     */
    protected $subsidizedLease;
    /**
     * @Serializer\Accessor(getter="getPaymentUniqueIdentifier")
     * @Serializer\SerializedName("Payment Unique Identifier")
     */
    protected $paymentUniqueIdentifier;
    /**
     * @Serializer\Accessor(getter="getMonthlyRentAmountDue")
     * @Serializer\SerializedName("Monthly Rent Amount Due")
     */
    protected $monthlyRentAmountDue;
    /**
     * @Serializer\Accessor(getter="getDueDate")
     * @Serializer\SerializedName("Due Date")
     */
    protected $dueDate;
    /**
     * @Serializer\Accessor(getter="getAmountPaid")
     * @Serializer\SerializedName("Amount Paid")
     */
    protected $amountPaid;
    /**
     * @Serializer\Accessor(getter="getDatePaid")
     * @Serializer\SerializedName("Date Paid")
     */
    protected $datePaid;
    /**
     * @Serializer\Accessor(getter="getLateIndicator")
     * @Serializer\SerializedName("Late Indicator")
     */
    protected $lateIndicator;

    public function __construct(Contract $contract, Operation $operation = null)
    {
        $this->contract = $contract;
        $this->operation = $operation;
    }

    public function getDatePaid()
    {
        if ($this->operation) {
            return $this->operation->getCreatedAt()->format(self::EXPERIAN_REPORT_DATE_FORMAT);
        }

        return '';
    }

    public function getDueDate()
    {
        if ($this->operation) {
            return $this->operation->getPaidFor()->format(self::EXPERIAN_REPORT_DATE_FORMAT);
        }

        return $this->contract->getPaidTo()->format(self::EXPERIAN_REPORT_DATE_FORMAT);
    }

    public function getEnrollmentDate()
    {
        return $this->contract->getStartAt()->format(self::EXPERIAN_REPORT_DATE_FORMAT);
    }

    public function getLeaseEndDate()
    {
        if ($finishAt = $this->contract->getFinishAt()) {
            return $finishAt->format(self::EXPERIAN_REPORT_DATE_FORMAT);
        }

        return '';
    }

    public function getLeaseMoveOutDate()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            return $this->contract->getFinishAt()->format(self::EXPERIAN_REPORT_DATE_FORMAT);
        }

        return '';
    }

    public function getLeaseUniqueIdentifier()
    {
        return $this->contract->getId();
    }

    public function getMonthToMonthLease()
    {
        if ($this->contract->getStatus() == ContractStatus::CURRENT && $this->contract->getFinishAt() == null) {
            return 'YES';
        }

        return 'NO';
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

        return '';
    }

    public function getPaymentUniqueIdentifier()
    {
        if ($this->operation) {
            return $this->operation->getId();
        }

        return null;
    }

    public function getSubsidizedLease()
    {
        return 'NO';
    }

    /**
     * @return string
     */
    public function getTenantAddress1()
    {
        return $this->contract->getProperty()->getPropertyAddress()->getAddress();
    }

    public function getTenantAddress2()
    {
        if ($unit = $this->contract->getUnit()) {
            return $unit->getName();
        }

        return '';
    }

    public function getTenantAddressUniqueIdentifier()
    {
        $unit = $this->contract->getUnit();
        if (!$unit) {
            throw new InvalidContractException('Contract has no unit');
        }

        return $unit->getId();
    }

    public function getTenantCity()
    {
        return $this->contract->getProperty()->getCity();
    }

    public function getTenantDOB()
    {
        if ($dob = $this->contract->getTenant()->getDateOfBirth()) {
            return $dob->format(self::EXPERIAN_REPORT_DATE_FORMAT);
        }

        return '';
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
        if ($this->operation) {
            return $this->operation->getAmount();
        }

        return '';
    }

    public function getLateIndicator()
    {
        if ($this->operation) {
            $paidFor = $this->operation->getPaidFor();
            $paidAt = $this->operation->getCreatedAt();
            $interval = $paidFor->diff($paidAt)->format('%r%a');
            if ($interval >= 30) {
                return 'YES';
            }
        }

        return 'NO';
    }
}
