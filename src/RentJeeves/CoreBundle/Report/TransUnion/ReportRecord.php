<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use JMS\Serializer\Annotation as Serializer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;

class ReportRecord 
{
    /**
     * @Serializer\Exclude
     */
    protected $contract;

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
    /** @Serializer\Accessor(getter="getTenantAddress") */
    protected $firstLineOfAddress;                                      // 32
    protected $secondLineOfAddress = '                                ';// 32
    /** @Serializer\Accessor(getter="getTenantAddressCity") */
    protected $tenantAddressCity;                                       // 20
    /** @Serializer\Accessor(getter="getTenantAddressState") */
    protected $tenantAddressState;                                      // 2
    /** @Serializer\Accessor(getter="getTenantAddressZip") */
    protected $tenantAddressZip;                                        // 9
    protected $reserved4 = ' ';                                         // 1
    protected $residenceCode = 'R';                                     // 1

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function getAccountUpdateTimestamp()
    {
        return $this->contract->getUpdatedAt()->format('mdYHis');
    }

    public function getPropertyIdentificationNumber()
    {
        $unit = $this->contract->getUnit();
        if ($unit) {
            $propertyNumber = $unit->getId();
        } else {
            $propertyNumber = $this->contract->getProperty()->getId();
        }

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

    // cj_order.amount
    public function getLeasePaymentAmountConfirmed()
    {
        return str_pad('2200', 9, '0', STR_PAD_LEFT);
    }

    public function getLeaseStatus()
    {
        return '11';
    }

    public function getPaymentRating()
    {
        return ' ';
    }

    public function getRentalHistoryProfile()
    {
        return str_repeat('B', 24);
    }

    public function getLeaseDisputeCode()
    {
        return '  ';
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

    public function getTenantAddress()
    {
        $address = $this->contract->getTenant()->getDefaultAddress();

        $addressLine = sprintf('%s %s', $address->getNumber(), $address->getStreet());
        if ($unit = $address->getUnit()) {
            $addressLine = sprintf('%s # %s', $addressLine, $unit);
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
}
