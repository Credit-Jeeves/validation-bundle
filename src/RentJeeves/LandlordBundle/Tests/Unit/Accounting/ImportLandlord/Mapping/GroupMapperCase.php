<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Enum\GroupType;
use RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\GroupMapper;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class GroupMapperCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage [Mapping] : value with key 'login_id' not found
     */
    public function shouldThrowExceptionIfGetNonexistentValue()
    {
        $mapper = new GroupMapper($this->getAddressLookupService());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManagerMock());

        $mapper->map([]);
    }

    /**
     * @test
     *
     * @expectedException \RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException
     * @expectedExceptionMessage [Mapping] : Address (test test, test, test, test) is not found by AddressLookupService
     */
    public function shouldThrowExceptionIfAddressIsNotValid()
    {
        $mapper = new GroupMapper($this->getAddressLookupService());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $mapper->map([
            'login_id' => 'test',
            'll_address' => 'test test',
            'll_city' => 'test',
            'll_state' => 'test',
            'll_zipcode' => 'test',
        ]);
    }

    /**
     * @return array
     */
    public function correctValuesForNewGroup()
    {
        return [
            [
                [
                    'login_id' => 'testLoginId',
                    'company_name' => 'testCompanyName',
                    'll_unit' => 'testUnit',
                    'll_email' => 'testEmail@trololo.ua',
                    'll_address' => '50 Orange Street',
                    'll_city' => 'Brooklyn',
                    'll_state' => 'NY',
                    'll_zipcode' => '11201',
                ],
                'testCompanyName'
            ],
            [
                [
                    'login_id' => 'testLoginId',
                    'company_name' => '',
                    'first_name' => 'testFirstName',
                    'last_name' => 'testLastName',
                    'll_unit' => 'testUnit',
                    'll_email' => 'testEmail@trololo.ua',
                    'll_address' => '50 Orange Street',
                    'll_city' => 'Brooklyn',
                    'll_state' => 'NY',
                    'll_zipcode' => '11201',
                ],
                'testFirstName testLastName'
            ]
        ];
    }

    /**
     * @param array $data
     * @param string $groupName
     *
     * @test
     * @dataProvider correctValuesForNewGroup
     */
    public function shouldCreateGroupAndRelatedEntityIfAddressIsValid($data, $groupName)
    {
        $mapper = new GroupMapper(
            $this->getAddressLookupService(),
            [
                'fee_cc' => 1.2,
                'fee_ach' => 2.7,
                'division_id' => 111111111
            ]
        );
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $group = $mapper->map($data);

        $this->assertInstanceOf('\CreditJeeves\DataBundle\Entity\Group', $group, 'Mapper should create Group Entity');

        $this->assertEquals($groupName, $group->getName(), 'Group name should be ' . $groupName);
        $this->assertEquals(
            $groupName,
            $group->getMailingAddressName(),
            'Group mailing address name should be ' . $groupName
        );
        $this->assertEquals('50 Orange Street', $group->getStreetAddress1(), 'Invalid mapping for street address 1');
        $this->assertEquals('testUnit', $group->getStreetAddress2(), 'Invalid mapping for street address 2');
        $this->assertEquals('Brooklyn', $group->getCity(), 'Invalid mapping for city');
        $this->assertEquals('NY', $group->getState(), 'Invalid mapping for state');
        $this->assertEquals(
            OrderAlgorithmType::PAYDIRECT,
            $group->getOrderAlgorithm(),
            'Order Algorithm should be set to ' . OrderAlgorithmType::PAYDIRECT
        );
        $this->assertEquals(GroupType::RENT, $group->getType(), 'Group type should be set ' . GroupType::RENT);
        $this->assertEquals('testLoginId', $group->getExternalGroupId(), 'Invalid mapping for external group id');

        $this->assertNotNull($holding = $group->getHolding(), 'New holding should be created');
        $this->assertEquals('testEmail@trololo.ua', $holding->getName(), 'Invalid mapping for holding name');

        $this->assertNotNull($groupSetting = $group->getGroupSettings(), 'Group settings should be initialized');
        $this->assertEquals(
            PaymentProcessor::ACI,
            $groupSetting->getPaymentProcessor(),
            'Default payment processor should be ' . PaymentProcessor::ACI
        );
        $this->assertEquals(1.2, $groupSetting->getFeeCC(), 'Default fee for cc should be 1.2');
        $this->assertEquals(2.7, $groupSetting->getFeeACH(), 'Default fee for ach should be 2.7');
        $this->assertTrue($groupSetting->isPassedAch(), 'New groupSetting should isPassedAch');

        $this->assertNotNull(
            $depositAccount = $group->getRentDepositAccountForCurrentPaymentProcessor(),
            'Should be created new deposit account'
        );
        $this->assertEquals(111111111, $depositAccount->getMerchantName(), 'Merchant name should be 111111111');

        $this->assertTrue(
            $groupSetting->isAutoApproveContracts(),
            'New groupSetting should be isAutoApproveContracts'
        );
    }

    /**
     * @test
     */
    public function shouldReturnExistingGroupIfItFoundByExternalGroupId()
    {
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(1);
        $group->setExternalGroupId('testExternalId');

        $this->getEntityManager()->flush($group);

        $mapper = new GroupMapper($this->getAddressLookupService());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $returnedGroup = $mapper->map([
            'login_id' => 'testExternalId'
        ]);

        $this->assertEquals($group, $returnedGroup);
    }

    /**
     * @return AddressLookupInterface
     */
    protected function getAddressLookupService()
    {
        return $this->getContainer()->get('address_lookup_service');
    }
}
