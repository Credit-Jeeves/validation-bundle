<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Mapping;

use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\GroupMapper;

class GroupMapperCase extends AbstractMapperCase
{
    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage [Mapping] : value with key 'login_id' not found
     */
    public function shouldThrowExceptionIfGetNonexistentValue()
    {
        $mapper = new GroupMapper($this->getGeoCoder());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEmMock());

        $mapper->map([]);
    }

    /**
     * @test
     *
     * @expectedException \RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException
     * @expectedExceptionMessage [Mapping] : Address (test test, test, test, test) is not found by geocoder
     */
    public function shouldThrowExceptionIfAddressIsNotValid()
    {
        $mapper = new GroupMapper($this->getGeoCoder());
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
        $mapper = new GroupMapper($this->getGeoCoder());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $group = $mapper->map($data);

        $this->assertInstanceOf('\CreditJeeves\DataBundle\Entity\Group', $group);

        $this->assertEquals($groupName, $group->getName());
        $this->assertEquals($groupName, $group->getMailingAddressName());
        $this->assertEquals('50 Orange Street', $group->getStreetAddress1());
        $this->assertEquals('testUnit', $group->getStreetAddress2());
        $this->assertEquals('Brooklyn', $group->getCity());
        $this->assertEquals('NY', $group->getState());
        $this->assertEquals(OrderAlgorithmType::PAYDIRECT, $group->getOrderAlgorithm());
        $this->assertEquals('testLoginId', $group->getExternalGroupId());

        $this->assertNotNull($holding = $group->getHolding());
        $this->assertEquals('testEmail@trololo.ua', $holding->getName());

        $this->assertNotNull($groupSetting = $group->getGroupSettings());
        $this->assertEquals(PaymentProcessor::ACI, $groupSetting->getPaymentProcessor());

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

        $mapper = new GroupMapper($this->getGeoCoder());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $returnedGroup = $mapper->map([
            'login_id' => 'testExternalId'
        ]);

        $this->assertEquals($group, $returnedGroup);
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\GeoCoder
     */
    protected function getGeoCoder()
    {
        return $this->getContainer()->get('renttrack.geocoder');
    }
}
