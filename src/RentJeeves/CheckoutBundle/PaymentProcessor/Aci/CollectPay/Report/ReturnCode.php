<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report;

class ReturnCode
{
    /** @var array */
    protected static $codes = [
        'Q30' => 'Services not provided or Merchandise not received',
        'Q41' => 'Cancelled recurring transaction',
        'Q53' => 'Not as Described or Defective merchandise',
        'Q57' => 'Fraudulent multiple transactions',
        'Q60' => 'Requested copy illegible or Invalid',
        'Q62' => 'Counterfeit transaction',
        'Q70' => 'Account number on exception file',
        'Q71' => 'Declined authorization',
        'Q72' => 'No authorization obtained',
        'Q73' => 'Expired card',
        'Q74' => 'Late presentment',
        'Q75' => 'Cardholder does not recognize transaction',
        'Q76' => 'Incorrect transaction code',
        'Q77' => 'Non-matching account number',
        'Q79' => 'Requested transaction information not received',
        'Q80' => 'Incorrect transaction amount or account number',
        'Q82' => 'Duplicate',
        'Q83' => 'Fraudulent transaction (Card not present)',
        'Q85' => 'Credit not processed',
        'Q86' => 'Paid by other means',
        'Q90' => 'Services not rendered (ATM or Visa travelmoney)',
        'Q93' => 'Risk identification service (RIS)',
        'Q96' => 'Transaction exceeds limited amount',
        'Q818' => 'Fraudulent transaction (Card present)',
        '4801' => 'Requested transaction information not received',
        '4802' => 'Requested / Required information illegible or missing',
        '4807' => 'Warning bulletin file',
        '4808' => 'Requested / Required authorization not obtained',
        '4812' => 'Account number not on file',
        '4831' => 'Transaction amount differs',
        '4834' => 'Duplicate processing',
        '4835' => 'Card not valid or expired',
        '4837' => 'No cardholder authorization',
        '4840' => 'Fraudulent processing of transaction',
        '4841' => 'Canceled recurring transaction',
        '4842' => 'Late presentation',
        '4846' => 'Correct transaction currency code not provided',
        '4847' => 'Requested/Required Authorization Not Obtained and Fraudulent Transaction',
        '4849' => 'Questionable merchant activity',
        '4850' => 'Credit posted as a purchase',
        '4853' => 'Cardholder disputes - Services / merchandise defective or not as described',
        '4854' => 'Cardholder disputes - Not classified in other category (US cardholders only)',
        '4855' => 'Non receipt of merchandise',
        '4857' => 'Card activated telephone transactions',
        '4859' => 'Services not rendered',
        '4860' => 'Credit not processed',
        '4862' => 'Counterfeit Transaction - Magnetic stripe POS fraud',
        '4863' => 'Cardholder does not recognize - potential fraud (US Only)',
        '4870' => 'Chip Liability Shift',
        '4871' => 'Chip/PIN Liability Shift',
        '4999' => 'Domestic Chargeback Dispute (Europe Region Only)',
        'R00' => 'Disputed Transaction',
        'R01' => 'Insufficient Funds',
        'R02' => 'Account Closed',
        'R03' => 'No Account/Unable to Locate Account',
        'R04' => 'Invalid Account Number',
        'R05' => 'Reserved',
        'R06' => 'Returned per ODFI\'s Request',
        'R07' => 'Authorization Revoked by Customer',
        'R08' => 'Payment Stopped',
        'R09' => 'Uncollected Funds, below reserve/available',
        'R10' => 'Customer Advises Not Authorized',
        'R11' => 'Check Truncation Entry Return',
        'R12' => 'Branch Sold to Another DFI',
        'R13' => 'RDFI Not Qualified to Participate',
        'R14' => 'Representative Payee Deceased',
        'R15' => 'Beneficiary or Account Holder Deceased',
        'R16' => 'Account Frozen',
        'R17' => 'File Record Edit Criteria',
        'R20' => 'Non-transaction Account',
        'R21' => 'Invalid Company Identification',
        'R22' => 'Invalid Individual ID Number',
        'R23' => 'Credit Entry Refused by Receiver',
        'R24' => 'Duplicate Entry',
        'R29' => 'Corporate Customer Advises Not Authorized',
        'R99' => 'Failed Thompson Review or Previous Return',
    ];

    /**
     * @param $code
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
