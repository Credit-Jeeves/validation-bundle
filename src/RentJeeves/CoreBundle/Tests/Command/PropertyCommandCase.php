<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Command\PropertyCommand;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class PropertyCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function index()
    {
        $this->load(true);
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $propertyFirst = new Property();
        $propertyFirst->setArea('MI');
        $propertyFirst->setCountry('US');
        $propertyFirst->setCity('East Lansing');
        $propertyFirst->setStreet('Coleman Rd');
        $propertyFirst->setNumber('3850');
        $propertyFirst->setZip('48823');
        $propertyFirst->setLatitude('42.7723043');
        $propertyFirst->setLongtitude('-84.4863972');

        $propertySecond = clone $propertyFirst;
        $propertySecond->setLatitude('42.772304');
        $propertySecond->setLongtitude('-84.486397');

        $em->persist($propertyFirst);
        $em->persist($propertySecond);
        $em->flush();

        static::$kernel = null;
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new PropertyCommand());

        $command = $application->find('property:duplicate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );

        $result = explode('||', $commandTester->getDisplay());
        $this->assertRegExp('/3850 Coleman Rd, East Lansing, MI 48823 /', $result[0]);
    }
}
