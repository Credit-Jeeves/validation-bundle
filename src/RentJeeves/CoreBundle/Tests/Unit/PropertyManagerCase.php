<?php
namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use CreditJeeves\DataBundle\Entity\Group;

class PropertyManagerCase extends BaseTestCase
{
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
}
