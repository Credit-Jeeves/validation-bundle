<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DisputeCode;

class TransUnionReportRecord
{
    const LEASE_STATUS_TRANSFERRED = '05';
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
     * @var Contract
     *
     * @Serializer\Exclude
     */
    protected $contract;

    /**
     * @var \DateTime
     *
     * @Serializer\Exclude
     */
    protected $reportedMonth;

    /**
     * @Serializer\Exclude
     */
    protected $reportLeaseStatus;

    /**
     * PaidFor of the operation for the reported month.
     *
     * @var \DateTime
     *
     * @Serializer\Exclude
     */
    protected $paidFor;

    /**
     * @Serializer\Exclude
     */
    protected $totalOperationsAmount;

    /**
     * @Serializer\Exclude
     */
    protected $lastPaymentDate;

    /**
     * If operation for the reported month not found, use lastPaidFor to calculate unpaid interval.
     *
     * @var \DateTime
     *
     * @Serializer\Exclude
     */
    protected $lastPaidFor;
                                                                        // Field Length
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
    protected $leaseDuration = '001';                                   // 3
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
    /** @Serializer\Accessor(getter="getDateOfLastPayment") */
    protected $dateOfLastPayment;                                       // 8
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
    /** @Serializer\Accessor(getter="getContractAddressCity") */
    protected $contractAddressCity;                                       // 20
    /** @Serializer\Accessor(getter="getContractAddressState") */
    protected $contractAddressState;                                      // 2
    /** @Serializer\Accessor(getter="getContractAddressZip") */
    protected $contractAddressZip;                                        // 9
    protected $reserved4 = ' ';                                         // 1
    protected $residenceCode = 'R';                                     // 1

    public function __construct(
        Contract $contract,
        \DateTime $month,
        \DateTime $lastPaidFor,
        $paidFor = null,
        $amount = null,
        \DateTime $lastPaymentDate = null
    ) {
        $this->contract = $contract;
        $this->reportedMonth = $month;
        $this->paidFor = $paidFor;
        $this->totalOperationsAmount = $amount;
        $this->lastPaymentDate = $lastPaymentDate;
        $this->lastPaidFor = $lastPaidFor;
    }

    /**
     * @return Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * @return \DateTime
     */
    public function getLastPaidFor()
    {
        return $this->lastPaidFor;
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

        return str_pad(sprintf('p%s', $propertyNumber), 20);
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

    public function getLeaseAmount()
    {
        return $this->getFormattedRentAmount();
    }

    public function getLeasePaymentAmountConfirmed()
    {
        $amount = $this->totalOperationsAmount ?: 0;

        return str_pad((int) $amount, 9, '0', STR_PAD_LEFT);
    }

    public function getLeaseStatus()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            $this->reportLeaseStatus = self::LEASE_STATUS_CLOSED_AND_PAID;
            if ($this->contract->getUncollectedBalance() > 0) {
                $this->reportLeaseStatus = self::LEASE_STATUS_CLOSED_AND_UNPAID;
            }
        } else {
            $interval = $this->getUnpaidInterval();
            $this->reportLeaseStatus = $this->getLateLeaseStatus($interval);
        }

        return $this->reportLeaseStatus;
    }

    public function getPaymentRating()
    {
        if (!$this->reportLeaseStatus) {
            $this->getLeaseStatus();
        }
        if ($this->reportLeaseStatus == self::LEASE_STATUS_CLOSED_AND_PAID ||
            $this->reportLeaseStatus == self::LEASE_STATUS_TRANSFERRED
        ) {
            $interval = $this->getUnpaidInterval();
            $leaseStatus = $this->getLateLeaseStatus($interval);

            switch ($leaseStatus) {
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

        return ' ';
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

        $unpaidInterval = $this->getUnpaidInterval();
        $leaseStatus = $this->getLateLeaseStatus($unpaidInterval);
        // if lease status not current but contract not finished (then contract is late)
        if ($leaseStatus != self::LEASE_STATUS_CURRENT && $this->contract->getStatus() != ContractStatus::FINISHED) {
            if ($unpaidInterval > 3 and $unpaidInterval < 6) {
                return 'MM';
            }
            if ($unpaidInterval >= 6 and $unpaidInterval < 15) {
                return 'NN';
            }
            if ($unpaidInterval >= 15) {
                return 'OO';
            }
        } elseif ($this->contract->getStatus() == ContractStatus::FINISHED
                   && $this->contract->getUncollectedBalance() > 10) {
            // only return "skipped" if the balance is more than $10 to avoid silly disputes
            return 'SS';
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
        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            $uncollectedBalance = (int) $this->contract->getUncollectedBalance();

            return str_pad($uncollectedBalance, 9, '0', STR_PAD_LEFT);
        }

        return $this->getFormattedRentAmount();
    }

    public function getAmountPastDue()
    {
        $amount = $this->getBalance() > 0 ? $this->getBalance() : 0;

        return str_pad((int) $amount, 9, '0', STR_PAD_LEFT);
    }

    public function getOriginalChargeOffAmount()
    {
        if ($this->contract->getStatus() == ContractStatus::FINISHED
            && $uncollectedBalance = $this->contract->getUncollectedBalance()
        ) {
            return str_pad((int) $uncollectedBalance, 9, '0', STR_PAD_LEFT);
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

    public function getDateOfLastPayment()
    {
        if ($this->lastPaymentDate) {
            return $this->lastPaymentDate->format('mdY');
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
        if (!$this->contract->getProperty()->isSingle()) {
            $addressLine = $this->contract->getUnit()->getName();
        }

        return str_pad($addressLine, 32);
    }

    public function getContractAddressCity()
    {
        $city = $this->contract->getProperty()->getCity();

        return str_pad($city, 20);
    }

    public function getContractAddressState()
    {
        return $this->contract->getProperty()->getArea();
    }

    public function getContractAddressZip()
    {
        $zip = $this->contract->getProperty()->getZip();

        return str_pad($zip, 9, '0');
    }

    protected function getBalance()
    {
        $interval = $this->getUnpaidInterval();
        $leaseStatus = $this->getLateLeaseStatus($interval);
        // if lease status not current but contract not finished (then contract is late)
        if ($leaseStatus != self::LEASE_STATUS_CURRENT && $this->contract->getStatus() != ContractStatus::FINISHED) {
            $isIntegratedGroup = $this->contract->getGroup()->getGroupSettings()->getIsIntegrated();
            if ($isIntegratedGroup) {
                return $this->contract->getIntegratedBalance();
            } else {
                return $this->contract->getBalance();
            }
        }

        if ($this->contract->getStatus() == ContractStatus::FINISHED) {
            return $this->contract->getUncollectedBalance();
        }

        return 0;
    }

    protected function getFormattedRentAmount()
    {
        $rent = (int) $this->contract->getRent();

        return str_pad($rent, 9, '0', STR_PAD_LEFT);
    }

    /**
     * @return int
     */
    protected function getUnpaidInterval()
    {
        // If there is an existent operation paidFor for the reported month, then we count contract as paid
        if ($this->paidFor) {
            return 0;
        }

        // Find target paidFor for the reported month
        $requiredPaidFor = clone $this->reportedMonth;
        $requiredPaidFor->setDate(null, null, $this->contract->getDueDate());

        // lastPaidFor always exists since contracts are selected with join operations of type rent
        // if contract doesn't have any operations, we will not select it.
        $interval = $requiredPaidFor->diff($this->lastPaidFor)->format('%r%a');

        return (int) $interval;
    }

    private function getLateLeaseStatus($interval)
    {
        if ($interval >= 30 && $interval <= 59) {
            return self::LEASE_STATUS_30_59_DAYS_LATE;
        }
        if ($interval >= 60 && $interval <= 89) {
            return self::LEASE_STATUS_60_89_DAYS_LATE;
        }
        if ($interval >= 90 && $interval <= 119) {
            return self::LEASE_STATUS_90_119_DAYS_LATE;
        }
        if ($interval >= 120 && $interval <= 149) {
            return self::LEASE_STATUS_120_149_DAYS_LATE;
        }
        if ($interval >= 150 && $interval <= 179) {
            return self::LEASE_STATUS_150_179_DAYS_LATE;
        }
        if ($interval >= 180) {
            return self::LEASE_STATUS_MORE_THAN_180_DAYS_LATE;
        }

        return self::LEASE_STATUS_CURRENT;
    }
}
