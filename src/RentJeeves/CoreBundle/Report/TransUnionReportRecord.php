<?php

namespace RentJeeves\CoreBundle\Report;

use CreditJeeves\DataBundle\Entity\Operation;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use DateTime;
use RentJeeves\DataBundle\Enum\DisputeCode;

class TransUnionReportRecord
{
    const LEASE_STATUS_CURRENT = 11;
    const LEASE_STATUS_CLOSED_AND_PAID = 13;
    const LEASE_STATUS_CLOSED_AND_UNPAID = 97;
    const LEASE_STATUS_30_59_DAYS_LATE = 71;
    const LEASE_STATUS_60_89_DAYS_LATE = 78;
    const LEASE_STATUS_90_119_DAYS_LATE = 80;
    const LEASE_STATUS_120_149_DAYS_LATE = 82;
    const LEASE_STATUS_150_179_DAYS_LATE = 83;
    const LEASE_STATUS_MORE_THAN_180_DAYS_LATE = 84;

    /**
     * @Serializer\Exclude
     */
    protected $contract;

    /**
     * @Serializer\Exclude
     */
    protected $month;

    /**
     * @Serializer\Exclude
     */
    protected $year;

    /**
     * @Serializer\Exclude
     */
    protected $reportLeaseStatus;

    /**
     * @Serializer\Exclude
     */
    protected $operation;

    protected $recordLength = '0426';                                   // 4
    protected $processingIndicator = '1';                               // 1
    /** @Serializer\Accessor(getter="getAccountUpdateTimestamp") */
    protected $timeStamp;                                               // 14
    protected $correctionIndicator = ' ';                               // 1
    /** @Serializer\Accessor(getter="getPropertyIdentificationNumber") */
    protected $propertyIdentificationNumber;                            // 20
    protected $cycleIdentifier = '  ';                                  // 2
    /** @Serializer\Accessor(getter="getLeaseNumber") */
    protected $leaseNumber;                                             // 30
    protected $leaseType = 'O';                                         // 1
    protected $leaseAgreementType = '29';                               // 2
    /** @Serializer\Accessor(getter="getLeaseStartDate") */
    protected $leaseObligationStartDate;                                // 8
    protected $reserved1 = '000000000';                                 // 9
    /** @Serializer\Accessor(getter="getTotalRentalObligationAmount") */
    protected $totalRentalObligationAmount;                             // 9
    /** @Serializer\Accessor(getter="getLeaseDuration") */
    protected $leaseDuration;                                           // 3
    protected $leasePaymentFrequency = 'M';                             // 1
    /** @Serializer\Accessor(getter="getLeaseAmount") */
    protected $leaseAmount;                                             // 9
    /** @Serializer\Accessor(getter="getLeasePaymentAmountConfirmed") */
    protected $leasePaymentAmountConfirmed;                             // 9
    /** @Serializer\Accessor(getter="getLeaseStatus") */
    protected $leaseStatus;                                             // 2
    /** @Serializer\Accessor(getter="getPaymentRating") */
    protected $paymentRating;                                           // 1
    /** @Serializer\Accessor(getter="getRentalHistoryProfile") */
    protected $rentalHistoryProfile;                                    // 24
    /** @Serializer\Accessor(getter="getLeaseCommentCode") */
    protected $leaseCommentCode = '  ';                                 // 2
    /** @Serializer\Accessor(getter="getLeaseDisputeCode") */
    protected $leaseDisputeCode;                                        // 2
    /** @Serializer\Accessor(getter="getLeaseBalance") */
    protected $leaseBalance;                                            // 9
    /** @Serializer\Accessor(getter="getAmountPastDue") */
    protected $amountPastDue;                                           // 9
    /** @Serializer\Accessor(getter="getOriginalChargeOffAmount") */
    protected $originalChargeOffAmount;                                 // 9
    protected $dateOfAccountInformation = '        ';                   // 8
    protected $dateOfFirstDelinquency = '        ';                     // 8
    /** @Serializer\Accessor(getter="getDateClosed") */
    protected $dateClosed;                                              // 8
    protected $dateOfLastPayment = '        ';                          // 8
    protected $reserved2 = '                 ';                         // 17
    protected $tenantTransactionType = ' ';                             // 1
    /** @Serializer\Accessor(getter="getTenantSurname") */
    protected $tenantSurname;                                           // 25
    /** @Serializer\Accessor(getter="getTenantFirstname") */
    protected $tenantFirstname;                                         // 20
    protected $tenantMiddleName = '                    ';               // 20
    protected $generationCode = ' ';                                    // 1
    /** @Serializer\Accessor(getter="getTenantSSN") */
    protected $tenantSSN;                                               // 9
    /** @Serializer\Accessor(getter="getTenantDOB") */
    protected $tenantDOB;                                               // 8
    /** @Serializer\Accessor(getter="getTenantPhoneNumber") */
    protected $tenantPhoneNumber;                                       // 10
    protected $leaseRelationshipCode = '1';                             // 1
    protected $reserved3 = '  ';                                        // 2
    protected $countryCode = 'US';                                      // 2
    /** @Serializer\Accessor(getter="getFirstLineOfAddress") */
    protected $firstLineOfAddress;                                      // 32
    /** @Serializer\Accessor(getter="getSecondLineOfAddress") */
    protected $secondLineOfAddress;                                     // 32
    /** @Serializer\Accessor(getter="getTenantAddressCity") */
    protected $tenantAddressCity;                                       // 20
    /** @Serializer\Accessor(getter="getTenantAddressState") */
    protected $tenantAddressState;                                      // 2
    /** @Serializer\Accessor(getter="getTenantAddressZip") */
    protected $tenantAddressZip;                                        // 9
    protected $reserved4 = ' ';                                         // 1
    protected $residenceCode = 'R';                                     // 1

    public function __construct(Contract $contract, $month, $year, Operation $operation = null)
    {
        $this->contract = $contract;
        $this->month = $month;
        $this->year = $year;
        $this->operation = $operation;
    }

    public function getAccountUpdateTimestamp()
    {
        return $this->contract->getUpdatedAt()->format('mdYHis');
    }

    /**
     * TU does not want a unique unit ID, only property
     */
    public function getPropertyIdentificationNumber()
    {
        $propertyNumber = $this->contract->getProperty()->getId();

        return str_pad($propertyNumber, 20);
    }

    public function getLeaseNumber()
    {
        return str_pad($this->contract->getId(), 30);
    }

    public function getLeaseStartDate()
    {
        return $this->contract->getStartAt()->format('mdY');
    }

    public function getTotalRentalObligationAmount()
    {
        return $this->getFormattedRentAmount();
    }

    // rj_contract.finish_at - rj_contract.start_at
    // if month-to-month, put in '001'
    public function getLeaseDuration()
    {
        if ($this->contract->getFinishAt() == null) {
            $duration = 1;
        } else {
            $duration = $this->contract->getStartAt()->diff($this->contract->getFinishAt())->m;
        }

        return str_pad($duration, 3, '0', STR_PAD_LEFT);
    }

    public function getLeaseAmount()
    {
        return $this->getFormattedRentAmount();
    }

    public function getLeasePaymentAmountConfirmed()
    {
        $amount = 0;
        if ($this->operation) {
            $amount = $this->operation->getAmount();
        }

        return str_pad($amount, 9, '0', STR_PAD_LEFT);
    }

    public function getLeaseStatus()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            if ($this->contract->getUncollectedBalance() > 0) {
                return self::LEASE_STATUS_CLOSED_AND_UNPAID;
            }
            return self::LEASE_STATUS_CLOSED_AND_PAID;
        }

        if ($this->operation) {
            $paidFor = $this->operation->getPaidFor();
            $paidAt = $this->operation->getOrder()->getUpdatedAt();
            $interval = $paidFor->diff($paidAt)->format('%r%a');
            $this->reportLeaseStatus = $this->getLateLeaseStatus($interval);
        } else {
            // If we reach this point - contract is definitely late
            // paidTo is "zero point" for calculating days late
            $paidTo = $this->contract->getPaidTo();
            $requiredMonth = new DateTime("{$this->year}-{$this->month}-1");
            $lastDayOfRequiredMonth = new DateTime($requiredMonth->format('Y-m-t'));
            $interval = $paidTo->diff($lastDayOfRequiredMonth)->format('%r%a');
            $this->reportLeaseStatus = $this->getLateLeaseStatus($interval);
        }

        return $this->reportLeaseStatus;
    }

    public function getPaymentRating()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            return ' ';
        }

        if (!$this->reportLeaseStatus) {
            $this->getLeaseStatus();
        }

        switch ($this->reportLeaseStatus) {
            case self::LEASE_STATUS_30_59_DAYS_LATE:
                return 1;
            case self::LEASE_STATUS_60_89_DAYS_LATE:
                return 2;
            case self::LEASE_STATUS_90_119_DAYS_LATE:
                return 3;
            case self::LEASE_STATUS_120_149_DAYS_LATE:
                return 4;
            case self::LEASE_STATUS_150_179_DAYS_LATE:
                return 5;
            case self::LEASE_STATUS_MORE_THAN_180_DAYS_LATE:
                return 6;
            default:
                return 0;
        }
    }

    public function getRentalHistoryProfile()
    {
        return str_repeat('B', 24);
    }

    public function getLeaseCommentCode()
    {
        //MM‐Rent paid before day 6
        //NN‐Rent paid on day 6 or before day 15
        //OO‐Rent paid on or after day 15
        //PP‐Rent paid, but required a demand letter
        //QQ‐Eviction (non‐legal action)
        //RR‐ Eviction
        //SS‐ Rent unpaid, renter skipped, and did not fulfill remaining lease term

        $paidOnDay = $this->operation->getOrder()->getUpdatedAt()->format('j');
        switch ($paidOnDay) {
            case ($paidOnDay < 6):
                return 'MM';
            case ($paidOnDay >= 6 && $paidOnDay < 15):
                return 'NN';
            case ($paidOnDay >= 15):
                return 'OO';
        }

        return str_repeat(' ', 2);
    }

    public function getLeaseDisputeCode()
    {
        $disputeCode = $this->contract->getDisputeCode();
        if ($disputeCode != DisputeCode::DISPUTE_CODE_BLANK) {
            return $disputeCode;
        }

        return str_repeat(' ', 2);
    }

    public function getLeaseBalance()
    {
        return $this->getFormattedRentAmount();
    }

    public function getAmountPastDue()
    {
        return str_pad((int)$this->contract->getBalance(), 9, '0', STR_PAD_LEFT);
    }

    public function getOriginalChargeOffAmount()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED
            && $uncollectedBalance = $this->contract->getUncollectedBalance()
        ) {
            return str_pad((int)$uncollectedBalance, 9, '0', STR_PAD_LEFT);
        }

        return str_repeat('0', 9);
    }

    public function getDateClosed()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            return $this->contract->getFinishAt()->format('mdY');
        }

        return str_repeat(' ', 8);
    }

    public function getTenantSurname()
    {
        $surname = $this->contract->getTenant()->getLastName();

        return str_pad($surname, 25);
    }

    public function getTenantFirstname()
    {
        $name = $this->contract->getTenant()->getFirstName();

        return str_pad($name, 20);
    }

    public function getTenantSSN()
    {
        return $this->contract->getTenant()->getSsn();
    }

    public function getTenantDOB()
    {
        if ($dob = $this->contract->getTenant()->getDateOfBirth()) {
            return $dob->format('mdY');
        }

        return str_repeat(' ', 8);
    }

    public function getTenantPhoneNumber()
    {
        if (($phone = $this->contract->getTenant()->getPhone()) && strlen($phone) == 10) {
            return $phone;
        }

        return str_repeat(' ', 10);
    }

    public function getFirstLineOfAddress()
    {
        $property = $this->contract->getProperty();
        $addressLine = sprintf('%s %s', $property->getNumber(), $property->getStreet());

        return str_pad($addressLine, 32);
    }

    public function getSecondLineOfAddress()
    {
        $addressLine = '';
        if ($unit = $this->contract->getUnit()) {
            $addressLine = $unit->getName();
        }

        return str_pad($addressLine, 32);
    }

    public function getTenantAddressCity()
    {
        $city = $this->contract->getTenant()->getDefaultAddress()->getCity();

        return str_pad($city, 20);
    }

    public function getTenantAddressState()
    {
        return $this->contract->getTenant()->getDefaultAddress()->getArea();
    }

    public function getTenantAddressZip()
    {
        $zip = $this->contract->getTenant()->getDefaultAddress()->getZip();

        return str_pad($zip, 9, '0');
    }

    private function getFormattedRentAmount()
    {
        $rent = (int)$this->contract->getRent();

        return str_pad($rent, 9, '0', STR_PAD_LEFT);
    }

    private function getLateLeaseStatus($interval)
    {
        switch ($interval) {
            case ($interval >= 30 && $interval <= 59):
                $status = self::LEASE_STATUS_30_59_DAYS_LATE;
                break;
            case ($interval >= 60 && $interval <= 89):
                $status = $status = self::LEASE_STATUS_60_89_DAYS_LATE;
                break;
            case ($interval >= 90 && $interval <= 119):
                $status = $status = self::LEASE_STATUS_90_119_DAYS_LATE;
                break;
            case ($interval >= 120 && $interval <= 149):
                $status = $status = self::LEASE_STATUS_120_149_DAYS_LATE;
                break;
            case ($interval >= 150 && $interval <= 179):
                $status = $status = self::LEASE_STATUS_150_179_DAYS_LATE;
                break;
            case ($interval >= 180):
                $status = $status = self::LEASE_STATUS_MORE_THAN_180_DAYS_LATE;
                break;
            default:
                $status = $status = self::LEASE_STATUS_CURRENT;
        }

        return $status;
    }
}
