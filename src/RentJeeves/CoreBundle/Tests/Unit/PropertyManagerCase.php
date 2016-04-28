<?php
namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class PropertyManagerCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    public function getContainer()
    {
        static::$kernel = null;

        return parent::getContainer();
    }

    /**
     * @test
     */
    public function checkDuplicate()
    {
        $this->markTestSkipped('Move address fileds');
    }

    /**
     * @test
     */
    public function shouldGetNewSingleUnit()
    {
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyManager
         */
        $propertyProcess = $container->get('property.manager');
        $property = new Property();
        $propertyAddress = new PropertyAddress();
        $property->setPropertyAddress($propertyAddress);

        $property->addPropertyGroup(new Group());
        $propertyProcess->setupSingleProperty($property, ['doFlush' => false]);

        $this->assertEquals(1, $property->getUnits()->count());

        /** @var Unit $unit */
        $unit = $property->getUnits()->first();
        $this->assertEquals(Unit::SINGLE_PROPERTY_UNIT_NAME, $unit->getActualName());

    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /without a group/
     */
    public function shouldRequireGroupForNewSingleUnit()
    {
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyManager
         */
        $propertyProcess = $container->get('property.manager');
        $property = new Property();
        $propertyAddress = new PropertyAddress();
        $property->setPropertyAddress($propertyAddress);
        $propertyProcess->setupSingleProperty($property);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /multiple groups/
     */
    public function shouldFailIfMultipleGroupsForNewSingleUnit()
    {
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyManager
         */
        $propertyProcess = $container->get('property.manager');
        $property = new Property();
        $propertyAddress = new PropertyAddress();
        $property->setPropertyAddress($propertyAddress);
        $property->addPropertyGroup(new Group());
        $property->addPropertyGroup(new Group());
        $propertyProcess->setupSingleProperty($property);
    }

    public function invalidUnitNamesDataProvider()
    {
        return [
            [''],
            [Unit::SEARCH_UNIT_UNASSIGNED],
            [Unit::SINGLE_PROPERTY_UNIT_NAME],
        ];
    }

    /**
     * @param string $invalidUnitName
     *
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unit name is invalid.
     * @dataProvider invalidUnitNamesDataProvider
     */
    public function shouldFailIfUnitNameInvalid($invalidUnitName)
    {
        $propertyManager = new PropertyManager(
            $this->getEntityManagerMock(),
            $this->getGoogleMock(),
            $this->getAddressLookupServiceMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );

        $group = new Group();
        $property = new Property();

        $propertyManager->getOrCreateUnit($group, $property, $invalidUnitName);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CoreBundle\Services\Exception\PropertyManagerUnitOwnershipException
     */
    public function shouldFailIfUnitBelongsToAnotherGroup()
    {
        $propertyManager = new PropertyManager(
            $this->getEntityManagerMock(),
            $this->getGoogleMock(),
            $this->getAddressLookupServiceMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );

        $group = new Group();
        $this->writeIdAttribute($group, 1);
        $anotherGroup = new Group();
        $this->writeIdAttribute($anotherGroup, 2);
        $unitName = 'Unit-1';
        $unit = new Unit();
        $unit->setName($unitName);
        $unit->setGroup($anotherGroup);
        $property = new Property();
        $property->addUnit($unit);

        $propertyManager->getOrCreateUnit($group, $property, $unitName);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unit mapping is invalid.
     */
    public function shouldFailIfUnitMappingExistAndNotEquals()
    {
        $propertyManager = new PropertyManager(
            $this->getEntityManagerMock(),
            $this->getGoogleMock(),
            $this->getAddressLookupServiceMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );

        $group = new Group();
        $unitName = 'Unit-1';
        $externalUnitId = 'Ext_Unit_1';
        $anotherExternalUnitId = 'Ext_Unit_2';
        $unit = new Unit();
        $unit->setName($unitName);
        $unit->setGroup($group);
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId($anotherExternalUnitId);
        $unit->setUnitMapping($unitMapping);

        $property = new Property();
        $property->addUnit($unit);

        $propertyManager->getOrCreateUnit($group, $property, $unitName, $externalUnitId);
    }

    /**
     * @test
     */
    public function shouldGetExistUnit()
    {
        $propertyManager = new PropertyManager(
            $this->getEntityManagerMock(),
            $this->getGoogleMock(),
            $this->getAddressLookupServiceMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );

        $group = new Group();
        $unitName = 'Unit-1';
        $unit = new Unit();
        $this->writeIdAttribute($unit, 1);
        $unit->setName($unitName);
        $unit->setGroup($group);

        $property = new Property();
        $property->addUnit($unit);

        $unit = $propertyManager->getOrCreateUnit($group, $property, $unitName);

        $this->assertEquals(1, $unit->getId(), 'Should return exist unit.');
    }

    /**
     * @test
     */
    public function shouldCreateNewUnit()
    {
        $propertyManager = new PropertyManager(
            $this->getEntityManagerMock(),
            $this->getGoogleMock(),
            $this->getAddressLookupServiceMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );

        $group = new Group();
        $unitName = 'Unit-1';
        $anotherUnitName = 'Unit-2';
        $unit = new Unit();
        $this->writeIdAttribute($unit, 1);
        $unit->setName($unitName);
        $unit->setGroup($group);

        $property = new Property();
        $property->addUnit($unit);

        $unit = $propertyManager->getOrCreateUnit($group, $property, $anotherUnitName);

        $this->assertNull($unit->getId(), 'Should create new unit.');
        $this->assertEquals(strtolower($anotherUnitName), $unit->getActualName(), 'Should set unit name.');
        $this->assertTrue($property->getUnits()->contains($unit), 'Should add unit to property');
    }

    /**
     * @test
     */
    public function shouldAddUnitMapping()
    {
        $propertyManager = new PropertyManager(
            $this->getEntityManagerMock(),
            $this->getGoogleMock(),
            $this->getAddressLookupServiceMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );

        $group = new Group();
        $unitName = 'Unit-1';
        $externalUnitId = 'Ext_Unit_1';

        $property = new Property();

        $unit = $propertyManager->getOrCreateUnit($group, $property, $unitName, $externalUnitId);

        $this->assertNotNull($unit->getUnitMapping(), 'Should be created new unit mapping');
        $this->assertEquals(
            $externalUnitId,
            $unit->getUnitMapping()->getExternalUnitId(),
            'Should be set external unit id'
        );
    }

    /**
     * @return \RentJeeves\ComponentBundle\Service\Google|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGoogleMock()
    {
        return $this->getBaseMock('RentJeeves\ComponentBundle\Service\Google');
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface
     */
    protected function getAddressLookupServiceMock()
    {
        return $this->getBaseMock('RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface');
    }
}
