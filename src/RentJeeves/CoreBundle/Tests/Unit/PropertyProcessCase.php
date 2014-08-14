<?php
namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Entity\Property;

class PropertyProcessCase extends BaseTestCase
{
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
        $em = $container->get('doctrine.orm.entity_manager');

        $propertyFirst = new Property();
        $propertyFirst->setArea('MI');
        $propertyFirst->setCity('East Lansing');
        $propertyFirst->setStreet('Coleman Rd');
        $propertyFirst->setNumber('3850');
        $propertyFirst->setZip('48823');
        $propertyFirst->setLatitude('42.7723043');
        $propertyFirst->setLongtitude('-84.4863972');
        $propertyFirst->setCountry('US');

        $propertySecond = clone $propertyFirst;
        $propertySecond->setLatitude('42.772304');
        $propertySecond->setLongtitude('-84.486397');


        $propertyProcess->checkPropertyDuplicate(
            $propertyFirst,
            $saveToGoogle = true
        );

        $this->assertNotEmpty(
            $reference = $propertyFirst->getGoogleReference()
        );
        $this->assertNotEmpty($propertyFirst->getId());

        $propertyProcess->checkPropertyDuplicate(
            $propertySecond,
            $saveToGoogle = true
        );

        $this->assertEmpty($propertySecond->getId());
        static::$kernel = null;
        $kernel = $this->getKernel();
        $container = $kernel->getContainer();
        /**
         * @var $propertyProcess PropertyProcess
         */
        $em = $container->get('doctrine.orm.entity_manager');
        $properties = $em->getRepository("RjDataBundle:Property")->findBy(
            array(
                'zip'   => '48823',
                'number' => '3850'
            )
        );

        $this->assertEquals(1, count($properties));
    }
}
