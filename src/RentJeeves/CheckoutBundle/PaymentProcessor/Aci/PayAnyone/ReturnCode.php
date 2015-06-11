<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone;

class ReturnCode
{
    /**
     * @var array
     */
    protected static $codes = [
        'P01' => 'Undeliverable Address',
        'P02' => 'Insufficient Address',
        'P03' => 'P.O. Box Closed',
        'P04' => 'Forwarding Time Expired',
        'P05' => 'Address – Check Cannot be Mailed',
        'OTC' => 'Over the counter check biller return',
        'R02' => 'Account Closed',
        'R03' => 'No Account / Unable to locate account',
        'R04' => 'Invalid Account Number',
        'R05' => 'No prenotification on file',
        'R06' => 'Returned per ODFI\'s Request',
        'R07' => 'Authorization Revoked by Account Holder',
        'R09' => 'Uncollected Funds',
        'R10' => 'Draft not authorized by Account Holder',
        'R11' => 'Check Truncation Return',
        'R14' => 'Account Holder Deceased',
        'R15' => 'Beneficiary Deceased',
        'R16' => 'Account Frozen',
        'R18' => 'Improper effective entry date',
        'R19' => 'Amount field error',
        'R20' => 'Non-transaction Account',
        'R21' => 'Invalid Company Identification',
        'R22' => 'Invalid Individual ID Number',
        'R23' => 'Credit Entry Refused by Receiver',
        'R24' => 'Duplicate Entry',
        'R25' => 'Addenda Error',
        'R27' => 'Trace Number Error',
        'R28' => 'Routing Number Check Digit Error',
        'R30' => 'RDFI not participate in check truncation program',
        'R31' => 'Permissible Return Entry (CCD and CTX only)',
        'R32' => 'RDFI Non-Settlement',
        'R34' => 'Limited participation RDFI',
        'R35' => 'Return of Improper Debit Entry',
        'R36' => 'Return of Improper Credit Entry',
        'R98' => 'Improper Reversal',
        'B21' => 'Invalid biller identification number',
        'J10' => 'Addenda Error Code – non numeric value found in numeric field of addenda',
        'J18' => 'Improper effective entry date',
        'J19' => 'Amount field error',
        'J22' => 'Invalid customer account mask',
        'J23' => 'Check digit error',
        'J25' => 'Addenda format error',
        'J26' => 'Field format errors in the entry detail record',
        'J27' => 'Improper trace number',
        'J29' => 'Biller does not accept prenotes',
        'J32' => 'Non-initialized high or low values',
        'J42' => 'Entry Error Code – high/low non initialized value found on addenda record',
        'J65' => 'Payment Refused by Biller',
        '032' => 'Detail rejected as part of batch reject',
        '046' => 'Detail transaction code error',
        '047' => 'Detail amount field error. Transaction Rejected',
        '049' => 'Customer name cannot be spaces',
        '051' => 'Detail addenda indicator should be 0.',
        '052' => 'Detail trace sequence number invalid. Transaction Rejected.',
        '053' => 'Detail Trace Orig-id not on O/R master. Detail Rejected.',
        '054' => 'Customer account number format does not match mask',
        '073' => 'Detail trace originator does not match batch originator',
        '078' => 'Biller accepts guaranteed payments, reversal rejected',
        '079' => 'Biller does not accept or validate prenotes. Detail rejected',
        '080' => 'Detail amount field was non numeric. Moved. Zeros. Detail rejected.',
        '087' => 'Detail trace number must be numeric and greater than zero.',
        '090' => 'Invalid customer account number - customer account number does not pass check digit routine',
        '091' => 'Invalid Transaction Amount – Exceeds Limit',
        '092' => 'Invalid characters in customer account number',
        '093' => 'Biller Requires Reversals with Addenda. Reversal Rejected.',
        '100' => 'Payment detail addenda indicator not equal number of addendas. Detail Rejected',
        '141' => 'Trace number must be numeric and ascending. Detail Rejected.',
        '143' => 'Customer account number cannot be spaces',
        '144' => 'Customer account number not left justified. Detail Rejected',
        '150' => 'Biller does not accept or validate payments. Detail rejected.',
        '157' => 'Record contains a high/low non-initialized value',
        '209' => 'Invalid characters contained in consumer name field',
        '219' => 'Consumer name field not left justified, Detail Rejected.',
        '234' => 'Rejected due to stop request by Concentrator/Biller',
        '248' => 'Prenote transaction with transaction amount greater than zero',
        '257' => 'RPPS Id does not participate in CC line of business',
        '260' => 'Detail transaction code error. It does not match the entry description',
        '261' => 'The transaction code is not compatible with the service class code.'
    ];

    /**
     * @param string $code
     *
     * @return string
     */
    public static function getCodeMessage($code)
    {
        if (isset(self::$codes[$code])) {
            return self::$codes[$code];
        }

        return '';
    }
}
