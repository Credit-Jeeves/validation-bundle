<?php

namespace RentJeeves\TestBundle\ProfitStars\Mocks;

use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSCreditDebitReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSEventReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSSettlementBatch;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\CreditsandDebitsTransactionDetailReportResponse;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\GetCreditandDebitReportsResponse;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\GetHistoricalEventReportResponse;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSCreditDebitReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSEventReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSSettlementBatch;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSTransactionDetail;

class TransactionReportingClientMock
{
    /**
     * @return GetCreditandDebitReportsResponse
     */
    public static function getMockForGetCreditandDebitReports()
    {
        $firstReport = new WSCreditDebitReport();
        $firstReport->setBatchStatus('Processed');
        $firstReport->setEffectiveDate('2015-11-25T00:00:00');
        $firstReport->setBatchId(1339213958);
        $firstReport->setDescription('Settlement');
        $firstReport->setAmount('1195.0000');

        $secondReport = new WSCreditDebitReport();
        $secondReport->setBatchStatus('Processed');
        $secondReport->setEffectiveDate('2015-12-01T00:00:00');
        $secondReport->setBatchId(1344787538);
        $secondReport->setDescription('ACH Returns');
        $secondReport->setAmount('-1195.0000');

        $WSCreditDebitReport = [$firstReport, $secondReport];

        $report = new ArrayOfWSCreditDebitReport();
        $report->setWSCreditDebitReport($WSCreditDebitReport);

        $response = new GetCreditandDebitReportsResponse();
        $response->setGetCreditandDebitReportsResult($report);

        return $response;
    }

    /**
     * @param string $merchantId
     *
     * @return CreditsandDebitsTransactionDetailReportResponse
     */
    public static function getMockForCreditsandDebitsTransactionDetailReport($merchantId)
    {
        $batch1 = new WSSettlementBatch();
        $batch1->setEntryType('Adjustment');
        $batch1->setBatchDescription('ACH Return: NSF');
        $batch1->setReason('');
        $batch1->setAmount('-50.0000');

        $transactionDetails1 = new WSTransactionDetail();
        $transactionDetails1->setEntityId($merchantId);
        $transactionDetails1->setLocationId(1023318);
        $transactionDetails1->setCustomerNumber('192');
        $transactionDetails1->setPaymentOrigin('Signature_Original');
        $transactionDetails1->setAccountType('Checking');
        $transactionDetails1->setOperationType('Sale');
        $transactionDetails1->setTransactionStatus('Uncollected_NSF');
        $transactionDetails1->setSettlementStatus('Charged_Back');
        $transactionDetails1->setEffectiveDate('2015-11-24T00:00:00');
        $transactionDetails1->setTransactionDate('2015-11-24T15:41:24.297');
        $transactionDetails1->setDescription('Rent for Unit #3');
        $transactionDetails1->setSourceApplication('Merchant_Portal');
        $transactionDetails1->setOriginatingAs('ACH');
        $transactionDetails1->setAuthResponse('Success');
        $transactionDetails1->setTotalAmount('50.0000');
        $transactionDetails1->setReferenceNumber('FV22P4CFBA1');
        $transactionDetails1->setTransactionNumber('{9b095bfa-87cb-4137-95f6-1b8dab77149c}');
        $transactionDetails1->setField1('');
        $transactionDetails1->setField2('');
        $transactionDetails1->setField3('');
        $transactionDetails1->setDisplayAccountNumber('5678');
        $transactionDetails1->setEmailAddress('');
        $transactionDetails1->setNotificationMethod('Merchant_Notify');
        $transactionDetails1->setFaceFeeType('Face');

        $batch1->setTransactionDetails($transactionDetails1);

        $batch2 = new WSSettlementBatch();
        $batch2->setEntryType('Adjustment');
        $batch2->setBatchDescription('ACH Return: Account Closed ');
        $batch2->setReason('');
        $batch2->setAmount('-300.0000');

        $transactionDetails2 = new WSTransactionDetail();
        $transactionDetails2->setEntityId($merchantId);
        $transactionDetails2->setLocationId(1023318);
        $transactionDetails2->setCustomerNumber('193');
        $transactionDetails2->setPaymentOrigin('Signature_Original');
        $transactionDetails2->setAccountType('Checking');
        $transactionDetails2->setOperationType('Sale');
        $transactionDetails2->setTransactionStatus('Invalid__Closed_Account');
        $transactionDetails2->setSettlementStatus('Charged_Back');
        $transactionDetails2->setEffectiveDate('2015-11-24T00:00:00');
        $transactionDetails2->setTransactionDate('2015-11-24T16:32:37.02');
        $transactionDetails2->setDescription('Unit #234');
        $transactionDetails2->setSourceApplication('Merchant_Portal');
        $transactionDetails2->setOriginatingAs('ACH');
        $transactionDetails2->setAuthResponse('Success');
        $transactionDetails2->setTotalAmount('300.0000');
        $transactionDetails2->setReferenceNumber('ZGC825CFBA2');
        $transactionDetails2->setTransactionNumber('{f14ee386-ef80-4b2d-8fc0-0c712ae1973b}');
        $transactionDetails2->setField1('');
        $transactionDetails2->setField2('');
        $transactionDetails2->setField3('');
        $transactionDetails2->setDisplayAccountNumber('5678');
        $transactionDetails2->setEmailAddress('');
        $transactionDetails2->setNotificationMethod('Merchant_Notify');
        $transactionDetails2->setFaceFeeType('Face');

        $batch2->setTransactionDetails($transactionDetails2);

        $batch3 = new WSSettlementBatch();
        $batch3->setEntryType('Adjustment');
        $batch3->setBatchDescription('ACH Return: Unable to Locate Account ');
        $batch3->setReason('');
        $batch3->setAmount('-400.0000');

        $transactionDetails3 = new WSTransactionDetail();
        $transactionDetails3->setEntityId($merchantId);
        $transactionDetails3->setLocationId(1023318);
        $transactionDetails3->setCustomerNumber('197');
        $transactionDetails3->setPaymentOrigin('Signature_Original');
        $transactionDetails3->setAccountType('Checking');
        $transactionDetails3->setOperationType('Sale');
        $transactionDetails3->setTransactionStatus('Invalid__Closed_Account');
        $transactionDetails3->setSettlementStatus('Charged_Back');
        $transactionDetails3->setEffectiveDate('2015-11-24T00:00:00');
        $transactionDetails3->setTransactionDate('2015-11-24T16:35:01.26');
        $transactionDetails3->setDescription('');
        $transactionDetails3->setSourceApplication('Merchant_Portal');
        $transactionDetails3->setOriginatingAs('ACH');
        $transactionDetails3->setAuthResponse('Success');
        $transactionDetails3->setTotalAmount('400.0000');
        $transactionDetails3->setReferenceNumber('3MC825CFBA2');
        $transactionDetails3->setTransactionNumber('{c08b7f6f-de30-4a8a-a055-eab8f723c77d}');
        $transactionDetails3->setField1('');
        $transactionDetails3->setField2('');
        $transactionDetails3->setField3('');
        $transactionDetails3->setDisplayAccountNumber('5678');
        $transactionDetails3->setEmailAddress('');
        $transactionDetails3->setNotificationMethod('Merchant_Notify');
        $transactionDetails3->setFaceFeeType('Face');

        $batch3->setTransactionDetails($transactionDetails3);

        $batch4 = new WSSettlementBatch();
        $batch4->setEntryType('Adjustment');
        $batch4->setBatchDescription('ACH Return: Customer Advises Not Authorized Number');
        $batch4->setReason('');
        $batch4->setAmount('-195.0000');

        $transactionDetails4 = new WSTransactionDetail();
        $transactionDetails4->setEntityId($merchantId);
        $transactionDetails4->setLocationId(1023318);
        $transactionDetails4->setCustomerNumber('218');
        $transactionDetails4->setPaymentOrigin('Signature_Original');
        $transactionDetails4->setAccountType('Checking');
        $transactionDetails4->setOperationType('Sale');
        $transactionDetails4->setTransactionStatus('Disputed');
        $transactionDetails4->setSettlementStatus('Charged_Back');
        $transactionDetails4->setEffectiveDate('2015-11-24T00:00:00');
        $transactionDetails4->setTransactionDate('2015-11-24T16:39:06.837');
        $transactionDetails4->setDescription('');
        $transactionDetails4->setSourceApplication('Merchant_Portal');
        $transactionDetails4->setOriginatingAs('ACH');
        $transactionDetails4->setAuthResponse('Success');
        $transactionDetails4->setTotalAmount('195.0000');
        $transactionDetails4->setReferenceNumber('7VC825CFBA2');
        $transactionDetails4->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $transactionDetails4->setField1('');
        $transactionDetails4->setField2('');
        $transactionDetails4->setField3('');
        $transactionDetails4->setDisplayAccountNumber('5678');
        $transactionDetails4->setEmailAddress('');
        $transactionDetails4->setNotificationMethod('Merchant_Notify');
        $transactionDetails4->setFaceFeeType('Face');

        $batch4->setTransactionDetails($transactionDetails4);

        $batch5 = new WSSettlementBatch();
        $batch5->setEntryType('Adjustment');
        $batch5->setBatchDescription('ACH Return: Invalid Account Number  ');
        $batch5->setReason('');
        $batch5->setAmount('-250.0000');

        $transactionDetails5 = new WSTransactionDetail();
        $transactionDetails5->setEntityId($merchantId);
        $transactionDetails5->setLocationId(1023318);
        $transactionDetails5->setCustomerNumber('199');
        $transactionDetails5->setPaymentOrigin('Signature_Original');
        $transactionDetails5->setAccountType('Checking');
        $transactionDetails5->setOperationType('Sale');
        $transactionDetails5->setTransactionStatus('Invalid__Closed_Account');
        $transactionDetails5->setSettlementStatus('Charged_Back');
        $transactionDetails5->setEffectiveDate('2015-11-24T00:00:00');
        $transactionDetails5->setTransactionDate('2015-11-24T16:38:09.473');
        $transactionDetails5->setDescription('');
        $transactionDetails5->setSourceApplication('Merchant_Portal');
        $transactionDetails5->setOriginatingAs('ACH');
        $transactionDetails5->setAuthResponse('Success');
        $transactionDetails5->setTotalAmount('250.0000');
        $transactionDetails5->setReferenceNumber('CSC825CFBA2');
        $transactionDetails5->setTransactionNumber('{804dfcfb-2bd6-4364-89d4-9752e85aa4f6}');
        $transactionDetails5->setField1('');
        $transactionDetails5->setField2('');
        $transactionDetails5->setField3('');
        $transactionDetails5->setDisplayAccountNumber('5678');
        $transactionDetails5->setEmailAddress('');
        $transactionDetails5->setNotificationMethod('Merchant_Notify');
        $transactionDetails5->setFaceFeeType('Face');

        $batch5->setTransactionDetails($transactionDetails5);

        $result = new ArrayOfWSSettlementBatch();
        $result->setWSSettlementBatch([$batch1, $batch2, $batch3, $batch4, $batch5]);
        $response = new CreditsandDebitsTransactionDetailReportResponse();
        $response->setCreditsandDebitsTransactionDetailReportResult($result);

        return $response;
    }

    /**
     * @return GetHistoricalEventReportResponse
     */
    public static function getMockForGetHistoricalEventReport()
    {
        $report1 = new WSEventReport();
        $report1->setEventDateTime('2015-11-24T15:41:24.19');
        $report1->setEventType('Approved');
        $report1->setEventDatastring('Success');
        $report1->setTransactionStatus('Uncollected NSF');
        $report1->setPaymentType('Checking');
        $report1->setNameOnAccount('Jacinta Kimbrough');
        $report1->setTransactionNumber('{9b095bfa-87cb-4137-95f6-1b8dab77149c}');
        $report1->setReferenceNumber('FV22P4CFBA1');
        $report1->setCustomerNumber('192');
        $report1->setOperationType('Sale');
        $report1->setLocationName('Location 1');
        $report1->setTransactionDateTime('0001-01-01T00:00:00');
        $report1->setTotalAmount('50.0000');
        $report1->setOwnerAppReferenceId(0);

        $report2 = new WSEventReport();
        $report2->setEventDateTime('2015-11-24T16:32:36.867');
        $report2->setEventType('Approved');
        $report2->setEventDatastring('Success');
        $report2->setTransactionStatus('Invalid / Closed Account');
        $report2->setPaymentType('Checking');
        $report2->setNameOnAccount('Becky Swedlund');
        $report2->setTransactionNumber('{f14ee386-ef80-4b2d-8fc0-0c712ae1973b}');
        $report2->setReferenceNumber('ZGC825CFBA2');
        $report2->setCustomerNumber('193');
        $report2->setOperationType('Sale');
        $report2->setLocationName('Location 1');
        $report2->setTransactionDateTime('0001-01-01T00:00:00');
        $report2->setTotalAmount('300.0000');
        $report2->setOwnerAppReferenceId(0);

        $report3 = new WSEventReport();
        $report3->setEventDateTime('2015-11-24T16:35:01.07');
        $report3->setEventType('Approved');
        $report3->setEventDatastring('Success');
        $report3->setTransactionStatus('Invalid / Closed Account');
        $report3->setPaymentType('Checking');
        $report3->setNameOnAccount('Timmy Stark');
        $report3->setTransactionNumber('{c08b7f6f-de30-4a8a-a055-eab8f723c77d}');
        $report3->setReferenceNumber('3MC825CFBA2');
        $report3->setCustomerNumber('197');
        $report3->setOperationType('Sale');
        $report3->setLocationName('Location 1');
        $report3->setTransactionDateTime('0001-01-01T00:00:00');
        $report3->setTotalAmount('400.0000');
        $report3->setOwnerAppReferenceId(0);

        $report4 = new WSEventReport();
        $report4->setEventDateTime('2015-11-24T16:35:48.773');
        $report4->setEventType('Declined');
        $report4->setEventDatastring('Velocity Exceeded');
        $report4->setTransactionStatus('Declined');
        $report4->setPaymentType('Checking');
        $report4->setNameOnAccount('Patricia Rothwell');
        $report4->setTransactionNumber('{37e9b6b9-4058-4ac6-aa76-51f9bb67badc}');
        $report4->setReferenceNumber('ZMC825CFBA2');
        $report4->setCustomerNumber('198');
        $report4->setOperationType('Sale');
        $report4->setLocationName('Location 1');
        $report4->setTransactionDateTime('0001-01-01T00:00:00');
        $report4->setTotalAmount('950.0000');
        $report4->setOwnerAppReferenceId(0);

        $report5 = new WSEventReport();
        $report5->setEventDateTime('2015-11-24T16:36:07.087');
        $report5->setEventType('Declined');
        $report5->setEventDatastring('Velocity Exceeded');
        $report5->setTransactionStatus('Declined');
        $report5->setPaymentType('Checking');
        $report5->setNameOnAccount('Patricia Rothwell');
        $report5->setTransactionNumber('{37e9b6b9-4058-4ac6-aa76-51f9bb67badc}');
        $report5->setReferenceNumber('1PC825CFBA2');
        $report5->setCustomerNumber('198');
        $report5->setOperationType('Sale');
        $report5->setLocationName('Location 1');
        $report5->setTransactionDateTime('0001-01-01T00:00:00');
        $report5->setTotalAmount('550.0000');
        $report5->setOwnerAppReferenceId(0);

        $report6 = new WSEventReport();
        $report6->setEventDateTime('2015-11-24T16:36:33.21');
        $report6->setEventType('Declined');
        $report6->setEventDatastring('Velocity Exceeded');
        $report6->setTransactionStatus('Declined');
        $report6->setPaymentType('Checking');
        $report6->setNameOnAccount('Patricia Rothwell');
        $report6->setTransactionNumber('{37e9b6b9-4058-4ac6-aa76-51f9bb67badc}');
        $report6->setReferenceNumber('KPC825CFBA2');
        $report6->setCustomerNumber('198');
        $report6->setOperationType('Sale');
        $report6->setLocationName('Location 1');
        $report6->setTransactionDateTime('0001-01-01T00:00:00');
        $report6->setTotalAmount('550.0000');
        $report6->setOwnerAppReferenceId(0);

        $report7 = new WSEventReport();
        $report7->setEventDateTime('2015-11-24T16:38:09.29');
        $report7->setEventType('Approved');
        $report7->setEventDatastring('Success');
        $report7->setTransactionStatus('Invalid / Closed Account');
        $report7->setPaymentType('Checking');
        $report7->setNameOnAccount('Ronald Kline');
        $report7->setTransactionNumber('{804dfcfb-2bd6-4364-89d4-9752e85aa4f6}');
        $report7->setReferenceNumber('CSC825CFBA2');
        $report7->setCustomerNumber('199');
        $report7->setOperationType('Sale');
        $report7->setLocationName('Location 1');
        $report7->setTransactionDateTime('0001-01-01T00:00:00');
        $report7->setTotalAmount('250.0000');
        $report7->setOwnerAppReferenceId(0);

        $report8 = new WSEventReport();
        $report8->setEventDateTime('2015-11-24T16:39:06.667');
        $report8->setEventType('Approved');
        $report8->setEventDatastring('Success');
        $report8->setTransactionStatus('Disputed');
        $report8->setPaymentType('Checking');
        $report8->setNameOnAccount('Charles Bridges');
        $report8->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $report8->setReferenceNumber('7VC825CFBA2');
        $report8->setCustomerNumber('218');
        $report8->setOperationType('Sale');
        $report8->setLocationName('Location 1');
        $report8->setTransactionDateTime('0001-01-01T00:00:00');
        $report8->setTotalAmount('195.0000');
        $report8->setOwnerAppReferenceId(0);

        $report9 = new WSEventReport();
        $report9->setEventDateTime('2015-11-24T18:00:00');
        $report9->setEventType('Processed');
        $report9->setEventDatastring('');
        $report9->setTransactionStatus('Disputed');
        $report9->setPaymentType('Checking');
        $report9->setNameOnAccount('Charles Bridges');
        $report9->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $report9->setReferenceNumber('7VC825CFBA2');
        $report9->setCustomerNumber('218');
        $report9->setOperationType('Sale');
        $report9->setLocationName('Location 1');
        $report9->setTransactionDateTime('0001-01-01T00:00:00');
        $report9->setTotalAmount('195.0000');
        $report9->setOwnerAppReferenceId(0);

        $report10 = new WSEventReport();
        $report10->setEventDateTime('2015-11-24T18:00:00');
        $report10->setEventType('Processed');
        $report10->setEventDatastring('');
        $report10->setTransactionStatus('Invalid / Closed Account');
        $report10->setPaymentType('Checking');
        $report10->setNameOnAccount('Becky Swedlund');
        $report10->setTransactionNumber('{f14ee386-ef80-4b2d-8fc0-0c712ae1973b}');
        $report10->setReferenceNumber('ZGC825CFBA2');
        $report10->setCustomerNumber('193');
        $report10->setOperationType('Sale');
        $report10->setLocationName('Location 1');
        $report10->setTransactionDateTime('0001-01-01T00:00:00');
        $report10->setTotalAmount('300.0000');
        $report10->setOwnerAppReferenceId(0);

        $report11 = new WSEventReport();
        $report11->setEventDateTime('2015-11-24T18:00:00');
        $report11->setEventType('Processed');
        $report11->setEventDatastring('');
        $report11->setTransactionStatus('Invalid / Closed Account');
        $report11->setPaymentType('Checking');
        $report11->setNameOnAccount('Ronald Kline');
        $report11->setTransactionNumber('{804dfcfb-2bd6-4364-89d4-9752e85aa4f6}');
        $report11->setReferenceNumber('CSC825CFBA2');
        $report11->setCustomerNumber('199');
        $report11->setOperationType('Sale');
        $report11->setLocationName('Location 1');
        $report11->setTransactionDateTime('0001-01-01T00:00:00');
        $report11->setTotalAmount('250.0000');
        $report11->setOwnerAppReferenceId(0);

        $report12 = new WSEventReport();
        $report12->setEventDateTime('2015-11-24T18:00:00');
        $report12->setEventType('Processed');
        $report12->setEventDatastring('');
        $report12->setTransactionStatus('Invalid / Closed Account');
        $report12->setPaymentType('Checking');
        $report12->setNameOnAccount('Timmy Stark');
        $report12->setTransactionNumber('{c08b7f6f-de30-4a8a-a055-eab8f723c77d}');
        $report12->setReferenceNumber('3MC825CFBA2');
        $report12->setCustomerNumber('197');
        $report12->setOperationType('Sale');
        $report12->setLocationName('Location 1');
        $report12->setTransactionDateTime('0001-01-01T00:00:00');
        $report12->setTotalAmount('400.0000');
        $report12->setOwnerAppReferenceId(0);

        $report13 = new WSEventReport();
        $report13->setEventDateTime('2015-11-24T18:00:00');
        $report13->setEventType('Processed');
        $report13->setEventDatastring('');
        $report13->setTransactionStatus('Uncollected NSF');
        $report13->setPaymentType('Checking');
        $report13->setNameOnAccount('Jacinta Kimbrough');
        $report13->setTransactionNumber('{9b095bfa-87cb-4137-95f6-1b8dab77149c}');
        $report13->setReferenceNumber('FV22P4CFBA1');
        $report13->setCustomerNumber('192');
        $report13->setOperationType('Sale');
        $report13->setLocationName('Location 1');
        $report13->setTransactionDateTime('0001-01-01T00:00:00');
        $report13->setTotalAmount('50.0000');
        $report13->setOwnerAppReferenceId(0);

        $report14 = new WSEventReport();
        $report14->setEventDateTime('2015-11-25T00:00:00');
        $report14->setEventType('Originated');
        $report14->setEventDatastring('');
        $report14->setTransactionStatus('Disputed');
        $report14->setPaymentType('Checking');
        $report14->setNameOnAccount('Charles Bridges');
        $report14->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $report14->setReferenceNumber('7VC825CFBA2');
        $report14->setCustomerNumber('218');
        $report14->setOperationType('Sale');
        $report14->setLocationName('Location 1');
        $report14->setTransactionDateTime('0001-01-01T00:00:00');
        $report14->setTotalAmount('195.0000');
        $report14->setOwnerAppReferenceId(0);

        $report15 = new WSEventReport();
        $report15->setEventDateTime('2015-11-25T00:00:00');
        $report15->setEventType('Settled');
        $report15->setEventDatastring('');
        $report15->setTransactionStatus('Disputed');
        $report15->setPaymentType('Checking');
        $report15->setNameOnAccount('Charles Bridges');
        $report15->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $report15->setReferenceNumber('7VC825CFBA2');
        $report15->setCustomerNumber('218');
        $report15->setOperationType('Sale');
        $report15->setLocationName('Location 1');
        $report15->setTransactionDateTime('0001-01-01T00:00:00');
        $report15->setTotalAmount('195.0000');
        $report15->setOwnerAppReferenceId(0);

        $report16 = new WSEventReport();
        $report16->setEventDateTime('2015-11-25T00:00:00');
        $report16->setEventType('Originated');
        $report16->setEventDatastring('');
        $report16->setTransactionStatus('Invalid / Closed Account');
        $report16->setPaymentType('Checking');
        $report16->setNameOnAccount('Becky Swedlund');
        $report16->setTransactionNumber('{f14ee386-ef80-4b2d-8fc0-0c712ae1973b}');
        $report16->setReferenceNumber('ZGC825CFBA2');
        $report16->setCustomerNumber('193');
        $report16->setOperationType('Sale');
        $report16->setLocationName('Location 1');
        $report16->setTransactionDateTime('0001-01-01T00:00:00');
        $report16->setTotalAmount('300.0000');
        $report16->setOwnerAppReferenceId(0);

        $report17 = new WSEventReport();
        $report17->setEventDateTime('2015-11-25T00:00:00');
        $report17->setEventType('Settled');
        $report17->setEventDatastring('');
        $report17->setTransactionStatus('Invalid / Closed Account');
        $report17->setPaymentType('Checking');
        $report17->setNameOnAccount('Becky Swedlund');
        $report17->setTransactionNumber('{f14ee386-ef80-4b2d-8fc0-0c712ae1973b}');
        $report17->setReferenceNumber('ZGC825CFBA2');
        $report17->setCustomerNumber('193');
        $report17->setOperationType('Sale');
        $report17->setLocationName('Location 1');
        $report17->setTransactionDateTime('0001-01-01T00:00:00');
        $report17->setTotalAmount('300.0000');
        $report17->setOwnerAppReferenceId(0);

        $report18 = new WSEventReport();
        $report18->setEventDateTime('2015-11-25T00:00:00');
        $report18->setEventType('Originated');
        $report18->setEventDatastring('');
        $report18->setTransactionStatus('Invalid / Closed Account');
        $report18->setPaymentType('Checking');
        $report18->setNameOnAccount('Ronald Kline');
        $report18->setTransactionNumber('{804dfcfb-2bd6-4364-89d4-9752e85aa4f6}');
        $report18->setReferenceNumber('CSC825CFBA2');
        $report18->setCustomerNumber('199');
        $report18->setOperationType('Sale');
        $report18->setLocationName('Location 1');
        $report18->setTransactionDateTime('0001-01-01T00:00:00');
        $report18->setTotalAmount('250.0000');
        $report18->setOwnerAppReferenceId(0);

        $report19 = new WSEventReport();
        $report19->setEventDateTime('2015-11-25T00:00:00');
        $report19->setEventType('Settled');
        $report19->setEventDatastring('');
        $report19->setTransactionStatus('Invalid / Closed Account');
        $report19->setPaymentType('Checking');
        $report19->setNameOnAccount('Ronald Kline');
        $report19->setTransactionNumber('{804dfcfb-2bd6-4364-89d4-9752e85aa4f6}');
        $report19->setReferenceNumber('CSC825CFBA2');
        $report19->setCustomerNumber('199');
        $report19->setOperationType('Sale');
        $report19->setLocationName('Location 1');
        $report19->setTransactionDateTime('0001-01-01T00:00:00');
        $report19->setTotalAmount('250.0000');
        $report19->setOwnerAppReferenceId(0);

        $report20 = new WSEventReport();
        $report20->setEventDateTime('2015-11-25T00:00:00');
        $report20->setEventType('Originated');
        $report20->setEventDatastring('');
        $report20->setTransactionStatus('Invalid / Closed Account');
        $report20->setPaymentType('Checking');
        $report20->setNameOnAccount('Timmy Stark');
        $report20->setTransactionNumber('{c08b7f6f-de30-4a8a-a055-eab8f723c77d}');
        $report20->setReferenceNumber('3MC825CFBA2');
        $report20->setCustomerNumber('197');
        $report20->setOperationType('Sale');
        $report20->setLocationName('Location 1');
        $report20->setTransactionDateTime('0001-01-01T00:00:00');
        $report20->setTotalAmount('400.0000');
        $report20->setOwnerAppReferenceId(0);

        $report21 = new WSEventReport();
        $report21->setEventDateTime('2015-11-25T00:00:00');
        $report21->setEventType('Settled');
        $report21->setEventDatastring('');
        $report21->setTransactionStatus('Invalid / Closed Account');
        $report21->setPaymentType('Checking');
        $report21->setNameOnAccount('Timmy Stark');
        $report21->setTransactionNumber('{c08b7f6f-de30-4a8a-a055-eab8f723c77d}');
        $report21->setReferenceNumber('3MC825CFBA2');
        $report21->setCustomerNumber('197');
        $report21->setOperationType('Sale');
        $report21->setLocationName('Location 1');
        $report21->setTransactionDateTime('0001-01-01T00:00:00');
        $report21->setTotalAmount('400.0000');
        $report21->setOwnerAppReferenceId(0);

        $report22 = new WSEventReport();
        $report22->setEventDateTime('2015-11-25T00:00:00');
        $report22->setEventType('Originated');
        $report22->setEventDatastring('');
        $report22->setTransactionStatus('Uncollected_NSF');
        $report22->setPaymentType('Checking');
        $report22->setNameOnAccount('Jacinta Kimbrough');
        $report22->setTransactionNumber('{9b095bfa-87cb-4137-95f6-1b8dab77149c}');
        $report22->setReferenceNumber('FV22P4CFBA1');
        $report22->setCustomerNumber('192');
        $report22->setOperationType('Sale');
        $report22->setLocationName('Location 1');
        $report22->setTransactionDateTime('0001-01-01T00:00:00');
        $report22->setTotalAmount('50.0000');
        $report22->setOwnerAppReferenceId(0);

        $report23 = new WSEventReport();
        $report23->setEventDateTime('2015-11-25T00:00:00');
        $report23->setEventType('Originated');
        $report23->setEventDatastring('');
        $report23->setTransactionStatus('Uncollected NSF');
        $report23->setPaymentType('Checking');
        $report23->setNameOnAccount('Jacinta Kimbrough');
        $report23->setTransactionNumber('{9b095bfa-87cb-4137-95f6-1b8dab77149c}');
        $report23->setReferenceNumber('FV22P4CFBA1');
        $report23->setCustomerNumber('192');
        $report23->setOperationType('Sale');
        $report23->setLocationName('Location 1');
        $report23->setTransactionDateTime('0001-01-01T00:00:00');
        $report23->setTotalAmount('50.0000');
        $report23->setOwnerAppReferenceId(0);

        $report24 = new WSEventReport();
        $report24->setEventDateTime('2015-11-30T11:00:32.017');
        $report24->setEventType('Returned NSF');
        $report24->setEventDatastring('');
        $report24->setTransactionStatus('Uncollected NSF');
        $report24->setPaymentType('Checking');
        $report24->setNameOnAccount('Jacinta Kimbrough');
        $report24->setTransactionNumber('{9b095bfa-87cb-4137-95f6-1b8dab77149c}');
        $report24->setReferenceNumber('FV22P4CFBA1');
        $report24->setCustomerNumber('192');
        $report24->setOperationType('Sale');
        $report24->setLocationName('Location 1');
        $report24->setTransactionDateTime('0001-01-01T00:00:00');
        $report24->setTotalAmount('50.0000');
        $report24->setOwnerAppReferenceId(0);

        $report25 = new WSEventReport();
        $report25->setEventDateTime('2015-11-30T11:01:23.66');
        $report25->setEventType('Returned Bad Account');
        $report25->setEventDatastring('Account Closed ');
        $report25->setTransactionStatus('Invalid / Closed Account');
        $report25->setPaymentType('Checking');
        $report25->setNameOnAccount('Becky Swedlund');
        $report25->setTransactionNumber('{f14ee386-ef80-4b2d-8fc0-0c712ae1973b}');
        $report25->setReferenceNumber('ZGC825CFBA2');
        $report25->setCustomerNumber('193');
        $report25->setOperationType('Sale');
        $report25->setLocationName('Location 1');
        $report25->setTransactionDateTime('0001-01-01T00:00:00');
        $report25->setTotalAmount('300.0000');
        $report25->setOwnerAppReferenceId(0);

        $report26 = new WSEventReport();
        $report26->setEventDateTime('2015-11-30T11:02:15.89');
        $report26->setEventType('Returned Bad Account');
        $report26->setEventDatastring('Unable to Locate Account ');
        $report26->setTransactionStatus('Invalid / Closed Account');
        $report26->setPaymentType('Checking');
        $report26->setNameOnAccount('Timmy Stark');
        $report26->setTransactionNumber('{c08b7f6f-de30-4a8a-a055-eab8f723c77d}');
        $report26->setReferenceNumber('3MC825CFBA2');
        $report26->setCustomerNumber('197');
        $report26->setOperationType('Sale');
        $report26->setLocationName('Location 1');
        $report26->setTransactionDateTime('0001-01-01T00:00:00');
        $report26->setTotalAmount('400.0000');
        $report26->setOwnerAppReferenceId(0);

        $report27 = new WSEventReport();
        $report27->setEventDateTime('2015-11-30T11:03:19.22');
        $report27->setEventType('Returned Bad Account');
        $report27->setEventDatastring('Invalid Account Number  ');
        $report27->setTransactionStatus('Invalid / Closed Account');
        $report27->setPaymentType('Checking');
        $report27->setNameOnAccount('Ronald Kline');
        $report27->setTransactionNumber('{804dfcfb-2bd6-4364-89d4-9752e85aa4f6}');
        $report27->setReferenceNumber('CSC825CFBA2');
        $report27->setCustomerNumber('199');
        $report27->setOperationType('Sale');
        $report27->setLocationName('Location 1');
        $report27->setTransactionDateTime('0001-01-01T00:00:00');
        $report27->setTotalAmount('250.0000');
        $report27->setOwnerAppReferenceId(0);

        $report28 = new WSEventReport();
        $report28->setEventDateTime('2015-11-30T11:04:19.16');
        $report28->setEventType('Charged Back');// !!! WARNING
        $report28->setEventDatastring('NSF');
        $report28->setTransactionStatus('Disputed');
        $report28->setPaymentType('Checking');
        $report28->setNameOnAccount('Charles Bridges');
        $report28->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $report28->setReferenceNumber('7VC825CFBA2');
        $report28->setCustomerNumber('218');
        $report28->setOperationType('Sale');
        $report28->setLocationName('Location 1');
        $report28->setTransactionDateTime('0001-01-01T00:00:00');
        $report28->setTotalAmount('195.0000');
        $report28->setOwnerAppReferenceId(0);

        $result = new ArrayOfWSEventReport();
        $result->setWSEventReport([
            $report1,
            $report2,
            $report3,
            $report4,
            $report5,
            $report6,
            $report7,
            $report8,
            $report9,
            $report10,
            $report11,
            $report12,
            $report13,
            $report14,
            $report15,
            $report16,
            $report17,
            $report18,
            $report19,
            $report20,
            $report21,
            $report22,
            $report23,
            $report24,
            $report25,
            $report26,
            $report27,
            $report28,
        ]);

        $response = new GetHistoricalEventReportResponse();
        $response->setGetHistoricalEventReportResult($result);

        return $response;
    }
}
