<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Mapping;

use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping\UnitMapper;

class UnitMapperCase extends AbstractMapperCase
{
    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Please send the group as 2nd parameter for function map
     */
    public function shouldThrowExceptionIfGroupNotSend()
    {
        $mapper = new UnitMapper($this->getPropertyProcess());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEmMock());

        $mapper->map([]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage [Mapping] : value with key 'unitid' not found
     */
    public function shouldThrowExceptionIfGetNonexistentValue()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 4);

        $mapper = new UnitMapper($this->getPropertyProcess());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $mapper->map([], $group);
    }

    /**
     * @test
     *
     * @expectedException \RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException
     * @expectedExceptionMessage [Mapping] : Address (test , test, test, test) is not found by PropertyManager
     */
    public function shouldThrowExceptionIfAddressIsNotValid()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 4);

        $mapper = new UnitMapper($this->getPropertyProcess());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $mapper->map(
            [
                'unitid' => 'testUnitID',
                'unitnumber' => 'test',
                'streetaddress' => 'test',
                'city_name' => 'test',
                'state_name' => 'test',
                'zipcode' => 'test',
            ],
            $group
        );
    }

    /**
     * @test
     *
     * @expectedException \RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\DuplicatedUnitException
     * @expectedExceptionMessage [Mapping] : Unit with externalId#AAABBB-7 and Holding#5 already exists
     */
    public function shouldThrowExceptionIfUnitFoundByHoldingAndExternalId()
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 24);

        $mapper = new UnitMapper($this->getPropertyProcess());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());
        // AAABBB-7 - value from fixtures
        $mapper->map(['unitid' => 'AAABBB-7'], $group);
    }

    /**
     * @return array
     */
    public function correctValuesForNewUnit()
    {
        return [
            [
                [
                    'unitid' => 'testUnitID',
                    'unitnumber' => 'testUnitNumber',
                    'streetaddress' => '50 Orange Street',
                    'city_name' => 'Brooklyn',
                    'state_name' => 'NY',
                    'zipcode' => '11201',
                ],
                'testUnitNumber'
            ],
            [
                [
                    'unitid' => 'testUnitID',
                    'unitnumber' => '',
                    'streetaddress' => '50 Orange Street',
                    'city_name' => 'Brooklyn',
                    'state_name' => 'NY',
                    'zipcode' => '11201',
                ],
                '' // For SINGLE_PROPERTY
            ],
        ];
    }

    /**
     * @test
     * @dataProvider correctValuesForNewUnit
     */
    public function shouldCreateUnitAndRelatedEntityIfUnitNotFoundByExternalId($data, $unitName)
    {
        $group = $this->getEntityManager()->find('DataBundle:Group', 4);

        $mapper = new UnitMapper($this->getPropertyProcess());
        $mapper->setLogger($this->getLoggerMock());
        $mapper->setEntityManager($this->getEntityManager());

        $unit = $mapper->map($data, $group);

        $this->assertInstanceOf('\RentJeeves\DataBundle\Entity\Unit', $unit);

        $this->assertEquals($group, $unit->getGroup());
        $this->assertEquals($group->getHolding(), $unit->getHolding());
        $this->assertEquals($unitName, $unit->getName());

        $this->assertInstanceOf('\RentJeeves\DataBundle\Entity\Property', $property = $unit->getProperty());
        $propertyAddress = $property->getPropertyAddress();

        $this->assertEquals('Brooklyn', $propertyAddress->getCity());
        $this->assertContains('Orange St', $propertyAddress->getStreet());
        $this->assertEquals('50', $propertyAddress->getNumber());
        $this->assertEquals('11201', $propertyAddress->getZip());
        $this->assertTrue($group->getGroupProperties()->contains($property));

        $this->assertEquals('testUnitID', $unit->getUnitMapping()->getExternalUnitId());
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\PropertyManager
     */
    protected function getPropertyProcess()
    {
        return $this->getContainer()->get('property.manager');
    }
}
