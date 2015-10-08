<?php

namespace RentJeeves\CoreBundle\Tests\Unit\PaymentProcessorMigration\Mapper;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Mapper\AciProfileMapper;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord;
use RentJeeves\DataBundle\Entity\AciCollectPayGroupProfile;
use RentJeeves\DataBundle\Entity\AciCollectPayUserProfile;
use RentJeeves\DataBundle\Entity\AciImportProfileMap;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class AciProfileMapperCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldMapObjectIfItHasRelationWithUser()
    {
        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        /** @var DepositAccount $depositAccount */
        $depositAccount = $user->getActiveContracts()[0]->getGroup()->getDepositAccounts()->first();
        $depositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $depositAccount->setMerchantName('testMerchantName');
        $holding = $user->getActiveContracts()[0]->getGroup()->getHolding();

        $address = $user->getDefaultAddress();
        $address->setCity('123456789012345');

        $profile = new AciImportProfileMap();
        $profile->setUser($user);

        $this->writeIdAttribute($profile, 1);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertCount(
            5,
            $result,
            '5 records expected: 1 Consumer, 1 Billing Account, 3 Funding Accounts'
        );
        /** @var ConsumerRecord $consumerRecord */
        $consumerRecord = $result[0];
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $consumerRecord
        );

        $this->assertEquals(1, $consumerRecord->getProfileId());
        $this->assertEquals('testBusinessId', $consumerRecord->getBusinessId());
        $this->assertEquals($user->getUsername(), $consumerRecord->getUserName());
        $this->assertEquals($user->getUsername(), $consumerRecord->getPassword());
        $this->assertEquals($user->getFirstName(), $consumerRecord->getConsumerFirstName());
        $this->assertEquals($user->getLastName(), $consumerRecord->getConsumerLastName());
        $this->assertEquals($user->getEmail(), $consumerRecord->getPrimaryEmailAddress());
        $this->assertEquals((string) $address, $consumerRecord->getAddress1());
        $this->assertEquals('123456789012', $consumerRecord->getCity());
        $this->assertEquals($address->getArea(), $consumerRecord->getState());
        $this->assertEquals($address->getZip(), $consumerRecord->getZipCode());

        /** @var AccountRecord $accountRecord */
        $accountRecord = $result[1];
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $accountRecord
        );

        $this->assertEquals(1, $accountRecord->getProfileId());
        $this->assertEquals(
            BillingAccountManager::createUserBillingAccountNumber($user, $depositAccount->getMerchantName()),
            $accountRecord->getBillingAccountNumber(),
            'BillingAccountNumber is incorrect'
        );
        $this->assertEquals('testMerchantName', $accountRecord->getDivisionId());
        $this->assertEquals(
            $user->getFirstName() . ' ' . $user->getLastName(),
            $accountRecord->getNameOnBillingAccount()
        );
        $this->assertEquals((string) $address, $accountRecord->getAddress1());
        $this->assertEquals('123456789012', $accountRecord->getCity());
        $this->assertEquals($address->getArea(), $accountRecord->getState());
        $this->assertEquals($address->getZip(), $accountRecord->getZipCode());
        $this->assertEquals('testBusinessId', $accountRecord->getBusinessId());

        /** @var FundingRecord $fundingRecord */
        $fundingRecord = $result[2];
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $fundingRecord
        );

        $this->assertEquals(1, $fundingRecord->getProfileId());
        $this->assertEquals(
            $user->getPaymentAccounts()->first()->getToken(),
            $fundingRecord->getFundingAccountHolderAddress2()
        );
        $this->assertEquals('testBusinessId', $fundingRecord->getBusinessId());

        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $result[3]
        );
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $result[4]
        );
    }

    /**
     * @test
     */
    public function shouldNotMapToConsumerRecordIfUserHasAciCollectPayProfile()
    {
        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        $aciProfile = new AciCollectPayUserProfile();
        $aciProfile->setProfileId(1);

        $user->setAciCollectPayProfile($aciProfile);

        /** @var DepositAccount $depositAccount */
        $depositAccount = $user->getActiveContracts()[0]->getGroup()->getDepositAccounts()->first();
        $depositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $depositAccount->setMerchantName('testMerchantName');
        $holding = $user->getActiveContracts()[0]->getGroup()->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setUser($user);

        $this->writeIdAttribute($profile, 1);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertCount(
            4,
            $result,
            '4 records expected: 1 Account record, 3 Funding Account records'
        );

        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $result[0]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $result[1]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $result[2]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $result[3]
        );
    }

    /**
     * @test
     */
    public function shouldNotMapToAccountRecordIfGroupDoesNotHaveAciDepositAccount()
    {
        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);

        /** @var DepositAccount $depositAccount */
        $depositAccount = $user->getActiveContracts()[0]->getGroup()->getDepositAccounts()->first();
        $depositAccount->setPaymentProcessor(PaymentProcessor::HEARTLAND); // not ACI
        $depositAccount->setMerchantName('testMerchantName');
        $holding = $user->getActiveContracts()[0]->getGroup()->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setUser($user);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertEquals(4, count($result));
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $result[0]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $result[1]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $result[2]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $result[3]
        );
    }

    /**
     * @test
     */
    public function shouldNotMapToFundingRecordIfUserHas1AciPaymentAccount()
    {
        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        $user->getPaymentAccounts()->last()->setPaymentProcessor(PaymentProcessor::ACI); // ACI

        /** @var DepositAccount $depositAccount */
        $depositAccount = $user->getActiveContracts()[0]->getGroup()->getDepositAccounts()->first();
        $depositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $depositAccount->setMerchantName('testMerchantName');
        $holding = $user->getActiveContracts()[0]->getGroup()->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setUser($user);

        $this->writeIdAttribute($profile, 1);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertCount(
            2,
            $result,
            '2 records expected: Consumer record and Account record'
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $result[0]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $result[1]
        );
    }

    /**
     * @test
     */
    public function shouldMapObjectIfItHasRelationWithGroup()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 24);
        $depositAccount = new DepositAccount();
        $depositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $depositAccount->setMerchantName('testMerchantName');
        $group->addDepositAccount($depositAccount);

        $billingAccount = new BillingAccount();
        $billingAccount->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $billingAccount->setToken('testToken');
        $group->addBillingAccount($billingAccount);
        /** @var Landlord $landlord */
        $landlord = $group->getGroupAgents()->first();
        $address = $landlord->getDefaultAddress();

        $holding = $group->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setGroup($group);

        $this->writeIdAttribute($profile, 1);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertEquals(3, count($result));
        /** @var ConsumerRecord $consumerRecord */
        $consumerRecord = $result[0];
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $consumerRecord
        );

        $this->assertEquals(1, $consumerRecord->getProfileId());
        $this->assertEquals('testBusinessId', $consumerRecord->getBusinessId());
        $this->assertEquals(md5('G' . $group->getId()), $consumerRecord->getUserName());
        $this->assertEquals(md5('G' . $group->getId()), $consumerRecord->getPassword());
        $this->assertEquals($landlord->getFirstName(), $consumerRecord->getConsumerFirstName());
        $this->assertEquals($landlord->getLastName(), $consumerRecord->getConsumerLastName());
        $this->assertEquals($landlord->getEmail(), $consumerRecord->getPrimaryEmailAddress());
        $this->assertEquals((string) $address, $consumerRecord->getAddress1());
        $this->assertEquals($address->getCity(), $consumerRecord->getCity());
        $this->assertEquals($address->getArea(), $consumerRecord->getState());
        $this->assertEquals($address->getZip(), $consumerRecord->getZipCode());
        /** @var AccountRecord $accountRecord */
        $accountRecord = $result[1];
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $accountRecord
        );

        $this->assertEquals(1, $accountRecord->getProfileId());
        $this->assertEquals(
            BillingAccountManager::createGroupBillingAccountNumber($group, $accountRecord->getDivisionId()),
            $accountRecord->getBillingAccountNumber(),
            'Group billing account number is incorrect'
        );
        $this->assertEquals('testBusinessId', $accountRecord->getDivisionId());
        $this->assertEquals($group->getName(), $accountRecord->getNameOnBillingAccount());
        $this->assertEquals($group->getStreetAddress1(), $accountRecord->getAddress1());
        $this->assertEquals($group->getCity(), $accountRecord->getCity());
        $this->assertEquals($group->getState(), $accountRecord->getState());
        $this->assertEquals($group->getZip(), $accountRecord->getZipCode());
        $this->assertEquals('testBusinessId', $accountRecord->getBusinessId());

        /** @var FundingRecord $fundingRecord */
        $fundingRecord = $result[2];
        $this->assertInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $fundingRecord
        );
        $this->assertEquals(1, $fundingRecord->getProfileId());
        $this->assertEquals($billingAccount->getToken(), $fundingRecord->getFundingAccountHolderAddress2());
        $this->assertEquals('testBusinessId', $fundingRecord->getBusinessId());
    }

    /**
     * @test
     */
    public function shouldNotMapToConsumerAndAccountRecordsIfGroupHasAciCollectPayProfile()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 24);
        $depositAccount = new DepositAccount();
        $depositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $depositAccount->setMerchantName('testMerchantName');
        $group->addDepositAccount($depositAccount);

        $aciProfile = new AciCollectPayGroupProfile();
        $aciProfile->setProfileId('testId');

        $group->setAciCollectPayProfile($aciProfile);

        $billingAccount = new BillingAccount();
        $billingAccount->setPaymentProcessor(PaymentProcessor::HEARTLAND);
        $billingAccount->setToken('testToken');
        $group->addBillingAccount($billingAccount);
        /** @var Landlord $landlord */
        $holding = $group->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setGroup($group);

        $this->writeIdAttribute($profile, 1);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertEquals(1, count($result));

        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord',
            $result[0]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord',
            $result[0]
        );
    }

    /**
     * @test
     */
    public function shouldNotMapToFundingRecordIfGroupHasAciBillingAccount()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 24);
        $depositAccount = new DepositAccount();
        $depositAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $depositAccount->setMerchantName('testMerchantName');
        $group->addDepositAccount($depositAccount);

        $billingAccount = new BillingAccount();
        $billingAccount->setPaymentProcessor(PaymentProcessor::ACI);
        $billingAccount->setToken('testToken');
        $group->addBillingAccount($billingAccount);

        $holding = $group->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setGroup($group);

        $this->writeIdAttribute($profile, 1);

        $mapper = new AciProfileMapper('testBusinessId', $this->getContainer()->get('deposit_account.repository'));
        $result = $mapper->map($profile, [$holding]);

        $this->assertEquals(2, count($result));

        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $result[0]
        );
        $this->assertNotInstanceOf(
            'RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord',
            $result[1]
        );
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayIfRelationUserDoesNotHaveAddressAndContracts()
    {
        /** @var AciProfileMapper|\PHPUnit_Framework_MockObject_MockObject $aciProfileMapper */
        $aciProfileMapper = $this->getMock(
            '\RentJeeves\CoreBundle\PaymentProcessorMigration\Mapper\AciProfileMapper',
            ['getContractForUser'],
            ['testBusinessId', $this->getEntityManager()->getRepository('RjDataBundle:DepositAccount')]
        );

        $aciProfileMapper->expects($this->once())
            ->method('getContractForUser')
            ->will($this->returnValue(null));

        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        $this->writeAttribute($user, 'addresses', []); // Fake removing addresses
        $holding = $depositAccount = $user->getActiveContracts()[0]->getGroup()->getHolding();

        $profile = new AciImportProfileMap();
        $profile->setUser($user);

        $result = $aciProfileMapper->map($profile, [$holding]);

        $this->assertTrue(is_array($result), 'Result of AciProfileMapper::map must be array');
        $this->assertTrue(
            empty($result),
            'Result of AciProfileMapper::map for User without Addresses and without Contracts must be empty'
        );
    }
}
