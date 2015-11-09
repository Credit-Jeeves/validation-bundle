<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Mapping;

use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\LandlordMapper;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class LandlordMapperCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Please send the group as 2nd parameter for function map
     */
    public function shouldThrowExceptionIfGroupNotSend()
    {
        $mapper = new LandlordMapper('en');
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManagerMock());

        $mapper->map([]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage [Mapping] : value with key 'landlordid' not found
     */
    public function shouldThrowExceptionIfGetNonexistentValue()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 4);

        $mapper = new LandlordMapper('en');
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $mapper->map([], $group);
    }

    /**
     * @test
     */
    public function shouldCreateLandlordAndRelatedEntityIfLandlordNotFoundByHoldingAndExternalId()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 4);

        $mapper = new LandlordMapper('en');
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $landlord = $mapper->map(
            [
                'landlordid' => 'testLandlordID',
                'first_name' => 'testFirstName',
                'last_name' => 'testLastName',
                'll_email' => 'testEmail@trololo.ua',
                'll_phone' => '1231231231',
            ],
            $group
        );

        $this->assertInstanceOf('\RentJeeves\DataBundle\Entity\Landlord', $landlord);

        $this->assertEquals('testFirstName', $landlord->getFirstName());
        $this->assertEquals('testLastName', $landlord->getLastName());
        $this->assertEquals('testEmail@trololo.ua', $landlord->getEmail());
        $this->assertEquals('1231231231', $landlord->getPhone());
        $this->assertEquals('testLandlordID', $landlord->getExternalLandlordId());
        $this->assertEquals($group->getHolding(), $landlord->getHolding());
        $this->assertCount(1, $landlord->getAgentGroups());
        $this->assertEquals($group, $landlord->getAgentGroups()->first());
    }

    /**
     * @test
     */
    public function shouldReturnExistingLandlordIfItFoundByExternalId()
    {
        $this->load(true);

        $group = $this->getEntityManager()->find('DataBundle:Group', 24);
        $landlord = $this->getEntityManager()->find('RjDataBundle:Landlord', 65);
        $landlord->setExternalLandlordId('testLandlordID');

        $this->getEntityManager()->flush($landlord);

        $mapper = new LandlordMapper('en');
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $returnedLandlord = $mapper->map(
            [
                'landlordid' => 'testLandlordID',
            ],
            $group
        );

        $this->assertEquals($landlord, $returnedLandlord);
    }
}
