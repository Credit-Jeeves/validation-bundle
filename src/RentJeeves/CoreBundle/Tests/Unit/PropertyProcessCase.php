<?php
namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use CreditJeeves\DataBundle\Entity\Group;
use RuntimeException;

class PropertyProcessCase extends BaseTestCase
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
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyProcess
         */
        $propertyProcess = $container->get('property.process');

        $propertyFirst = new Property();
        $propertyFirst->setArea('MI');
        $propertyFirst->setCity('East Lansing');
        $propertyFirst->setStreet('Coleman Road');
        $propertyFirst->setNumber('3850');
        $propertyFirst->setZip('48823');
        $propertyFirst->setLatitude(42.7723043);
        $propertyFirst->setLongitude(-84.4863972);
        $propertyFirst->setCountry('US');

        $propertySecond = clone $propertyFirst;
        $propertySecond->setLatitude(42.772304);
        $propertySecond->setLongitude(-84.486397);


        $property = $propertyProcess->checkPropertyDuplicate(
            $propertyFirst,
            $saveToGoogle = true
        );
        $this->assertNotEmpty($property);
        $this->assertNotEmpty(
            $reference = $property->getGoogleReference()
        );
        $this->assertNotEmpty($propertyFirst->getId());
        //END checking first property

        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyProcess
         */
        $propertyProcess = $container->get('property.process');
        $propertyProcess->checkPropertyDuplicate(
            $propertySecond,
            $saveToGoogle = true
        );
        $this->assertEmpty($propertySecond->getId());
        $em = $container->get('doctrine.orm.entity_manager');
        $properties = $em->getRepository("RjDataBundle:Property")->findBy(
            array(
                'zip'   => '48823',
                'number' => '3850'
            )
        );

        $this->assertEquals(1, count($properties));
    }

    /**
     * @test
     */
    public function shouldGetNewSingleUnit()
    {
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyProcess
         */
        $propertyProcess = $container->get('property.process');
        $property = new Property();
        $property->addPropertyGroup(new Group());
        $propertyProcess->setupSingleProperty($property, ['doFlush' => false]);

        $this->assertEquals(1, $property->getUnits()->count());

        /** @var Unit $unit */
        $unit = $property->getUnits()->first();
        $this->assertEquals(Unit::SINGLE_PROPERTY_UNIT_NAME, $unit->getActualName());

    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /without a group/
     */
    public function shouldRequireGroupForNewSingleUnit()
    {
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyProcess
         */
        $propertyProcess = $container->get('property.process');
        $property = new Property();
        $propertyProcess->setupSingleProperty($property);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /multiple groups/
     */
    public function shouldFailIfMultipleGroupsForNewSingleUnit()
    {
        $this->load(true);
        $container = $this->getContainer();
        /**
         * @var $propertyProcess PropertyProcess
         */
        $propertyProcess = $container->get('property.process');
        $property = new Property();
        $property->addPropertyGroup(new Group());
        $property->addPropertyGroup(new Group());
        $propertyProcess->setupSingleProperty($property);
    }
}
