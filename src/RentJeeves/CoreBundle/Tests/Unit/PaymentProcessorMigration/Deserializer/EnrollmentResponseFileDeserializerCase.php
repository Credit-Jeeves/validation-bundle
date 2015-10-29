<?php

namespace RentJeeves\CoreBundle\Tests\Unit\PaymentProcessorMigration\Deserializer;

use RentJeeves\CoreBundle\PaymentProcessorMigration\Deserializer\EnrollmentResponseFileDeserializer;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerResponseRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingResponseRecord;
use RentJeeves\TestBundle\BaseTestCase;

class EnrollmentResponseFileDeserializerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldDeserializeCsvFile()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@RjCoreBundle/Tests/Fixtures/PaymentProcessorMigration/fileForDeserializer.csv'
        );
        $deserializer = new EnrollmentResponseFileDeserializer();
        $data = $deserializer->deserialize($pathToFile);

        $this->assertEquals(3, count($data));
        /** @var ConsumerResponseRecord $consumerRecord */
        $consumerRecord = $data[0];
        $this->assertInstanceOf(
            '\RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerResponseRecord',
            $consumerRecord
        );
        $this->assertEquals('C', $consumerRecord->getRecordType());
        $this->assertEquals('profileId', $consumerRecord->getProfileId());
        $this->assertEquals('consumerProfileId', $consumerRecord->getConsumerProfileId());
        $this->assertEquals('businessId', $consumerRecord->getBusinessId());
        $this->assertEquals('userName', $consumerRecord->getUserName());
        $this->assertEquals('password', $consumerRecord->getPassword());
        $this->assertEquals('consumerFirstName', $consumerRecord->getConsumerFirstName());
        $this->assertEquals('consumerLastName', $consumerRecord->getconsumerLastName());
        $this->assertEquals('primaryEmailAddress', $consumerRecord->getPrimaryEmailAddress());
        $this->assertEquals('secondaryEmailAddress', $consumerRecord->getSecondaryEmailAddress());
        $this->assertEquals('challengeQuestion1', $consumerRecord->getChallengeQuestion1());
        $this->assertEquals('challengeAnswer1', $consumerRecord->getChallengeAnswer1());
        $this->assertEquals('challengeQuestion2', $consumerRecord->getChallengeQuestion2());
        $this->assertEquals('challengeAnswer2', $consumerRecord->getChallengeAnswer2());
        $this->assertEquals('address1', $consumerRecord->getAddress1());
        $this->assertEquals('address2', $consumerRecord->getAddress2());
        $this->assertEquals('city', $consumerRecord->getCity());
        $this->assertEquals('zipCode', $consumerRecord->getZipCode());
        $this->assertEquals('countryCode', $consumerRecord->getCountryCode());
        $this->assertEquals('phoneNumber', $consumerRecord->getPhoneNumber());
        $this->assertEquals('contactPhoneNumber', $consumerRecord->getContactPhoneNumber());
        $this->assertEquals('textAddress', $consumerRecord->getTextAddress());
        $this->assertEquals('status', $consumerRecord->getStatus());
        $this->assertEquals('rejectReason', $consumerRecord->getRejectReason());

        /** @var AccountResponseRecord $accountRecord */
        $accountRecord = $data[1];
        $this->assertInstanceOf(
            '\RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountResponseRecord',
            $accountRecord
        );
        $this->assertEquals('A', $accountRecord->getRecordType());
        $this->assertEquals('profileId', $accountRecord->getProfileId());
        $this->assertEquals('billingAccountId', $accountRecord->getBillingAccountId());
        $this->assertEquals('businessId', $accountRecord->getBusinessId());
        $this->assertEquals('billingAccountNumber', $accountRecord->getBillingAccountNumber());
        $this->assertEquals('nsfReturnCount', $accountRecord->getNsfReturnCount());
        $this->assertEquals('nameOnBillingAccount', $accountRecord->getNameOnBillingAccount());
        $this->assertEquals('billingAccountNickname', $accountRecord->getBillingAccountNickname());
        $this->assertEquals('city', $accountRecord->getCity());
        $this->assertEquals('state', $accountRecord->getState());
        $this->assertEquals('zipCode', $accountRecord->getZipCode());
        $this->assertEquals('countryCode', $accountRecord->getCountryCode());
        $this->assertEquals('paperBillOnOffFlag', $accountRecord->getPaperBillOnOffFlag());
        $this->assertEquals('viewBillDetailFlag', $accountRecord->getViewBillDetailFlag());
        $this->assertEquals('divisionId', $accountRecord->getDivisionId());

        /** @var FundingResponseRecord $fundingRecord */
        $fundingRecord = $data[2];
        $this->assertInstanceOf(
            '\RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingResponseRecord',
            $fundingRecord
        );
        $this->assertEquals('F', $fundingRecord->getRecordType());
        $this->assertEquals('profileId', $fundingRecord->getProfileId());
        $this->assertEquals('filer1', $fundingRecord->getFiler1());
        $this->assertEquals('fundingAccountId', $fundingRecord->getFundingAccountId());
        $this->assertEquals('fundingAccountNickname', $fundingRecord->getFundingAccountNickname());
        $this->assertEquals('fundingAccountType', $fundingRecord->getFundingAccountType());
        $this->assertEquals('filer2', $fundingRecord->getFiler2());
        $this->assertEquals('fundingAccountHolderName', $fundingRecord->getFundingAccountHolderName());
        $this->assertEquals('fundingAccountHolderAddress1', $fundingRecord->getFundingAccountHolderAddress1());
        $this->assertEquals('fundingAccountHolderAddress2', $fundingRecord->getFundingAccountHolderAddress2());
        $this->assertEquals('fundingAccountHolderCity', $fundingRecord->getFundingAccountHolderCity());
        $this->assertEquals('fundingAccountHolderState', $fundingRecord->getFundingAccountHolderState());
        $this->assertEquals('fundingAccountHolderZipCode', $fundingRecord->getFundingAccountHolderZipCode());
        $this->assertEquals('fundingAccountHolderCountryCode', $fundingRecord->getFundingAccountHolderCountryCode());
        $this->assertEquals('fundingAccountPhoneNumber', $fundingRecord->getFundingAccountPhoneNumber());
        $this->assertEquals('routingNumber', $fundingRecord->getRoutingNumber());
        $this->assertEquals('bankAccountNumber', $fundingRecord->getBankAccountNumber());
        $this->assertEquals('bankAccountSubType', $fundingRecord->getBankAccountSubType());
        $this->assertEquals('cardExpirationMonth', $fundingRecord->getCardExpirationMonth());
        $this->assertEquals('creditCardSubType', $fundingRecord->getCreditCardSubType());
        $this->assertEquals('cardExpirationYear', $fundingRecord->getCardExpirationYear());
        $this->assertEquals('cardNumber', $fundingRecord->getCardNumber());
        $this->assertEquals('cardRoute', $fundingRecord->getCardRoute());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File not found or not readable
     */
    public function shouldThrowExceptionIfFileIsNotExists()
    {
        $pathToFile = '/test.csv';
        $deserializer = new EnrollmentResponseFileDeserializer();
        $deserializer->deserialize($pathToFile);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
