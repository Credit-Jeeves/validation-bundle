<?php

namespace RentJeeves\CoreBundle\Report\TransUnion;

use JMS\Serializer\Annotation as Serializer;

class ReportRecord 
{
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
    /** @Serializer\Accessor(getter="getDateOfFirstDelinquency") */
    protected $dateOfFirstDelinquency;                                  // 8
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

    public function getAccountUpdateTimestamp()
    {
        return '03152014171849';
    }

    public function getPropertyIdentificationNumber()
    {
        return str_pad('71', 20);
    }

    public function getLeaseNumber()
    {
        return str_pad('1171', 30);
    }

    public function getLeaseStartDate()
    {
        return '02012014';
    }

    // rj_contract.rent
    public function getTotalRentalObligationAmount()
    {
        return str_pad('2200', 9, '0', STR_PAD_LEFT);
    }

    // rj_contract.finish_at - rj_contract.start_at
    // if month-to-month, put in '001'
    public function getLeaseDuration()
    {
        return str_pad('5', 3, '0', STR_PAD_LEFT);
    }

    // rj_contract.rent
    public function getLeaseAmount()
    {
        return str_pad('2200', 9, '0', STR_PAD_LEFT);
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

    // // rj_contract.rent
    public function getLeaseBalance()
    {
        return str_pad('2200', 9, '0', STR_PAD_LEFT);
    }

    public function getAmountPastDue()
    {
        return str_repeat('0', 9);
    }

    public function getOriginalChargeOffAmount()
    {
        return str_repeat('0', 9);
    }

    public function getDateOfFirstDelinquency()
    {
        return str_repeat(' ', 8);
    }

    public function getDateClosed()
    {
        return str_repeat(' ', 8);
    }

    public function getDateOfLastPayment()
    {
        return '03022014';
    }

    public function getTenantSurname()
    {
        return str_pad('EATON', 25);
    }

    public function getTenantFirstname()
    {
        return str_pad('DARRYL', 20);
    }

    public function getTenantSSN()
    {
        return '555121212';
    }

    public function getTenantDOB()
    {
        return '05151955';
    }

    public function getTenantPhoneNumber()
    {
        return str_repeat(' ', 10);
    }

    public function getTenantAddress()
    {
        return str_pad('960 ANDANTE RD', 32);
    }

    public function getTenantAddressCity()
    {
        return str_pad('SANTA BARBARA', 20);
    }

    public function getTenantAddressState()
    {
        return 'CA';
    }

    public function getTenantAddressZip()
    {
        return str_pad('93105', 9, '0');
    }
}
