<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Command\PropertyCommand;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class PropertyCommandCase extends BaseTestCase
{
    /**
     * @todo: need refactoring
     *
     * @test
     */
    public function checkDuplicateProperty()
    {
        $this->markTestSkipped('Move address fileds');
        $this->load(true);
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $propertiesList = array();
        for ($i = 0; $i <= 10; $i++) {
            $property = new Property();
            $propertyAddress = new PropertyAddress();
            $property->setPropertyAddress($propertyAddress);

            $propertyAddress->setState('MI');
            $propertyAddress->setCity('East Lansing');
            $propertyAddress->setStreet('Coleman Rd');
            $propertyAddress->setNumber('3850');
            if ($i % 2) {
                $propertyAddress->setZip('48823');
            } elseif ($i % 3) {
                $propertyAddress->setZip('33333');
            } else {
                $propertyAddress->setZip('4444');
            }
            $propertyAddress->setLat('42.7723043');
            $propertyAddress->setLong('-84.4863972');
            $em->persist($property);
            $propertiesList[] = $property;
        }

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
        $this->assertEquals(12, count($result));
        $this->assertRegExp('/3850 Coleman Rd, East Lansing, MI 33333 /', $result[0]);

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->findAll();

        foreach ($contracts as $key => $contract) {
            if (!isset($propertiesList[$key])) {
                break;
            }
            $property = $propertiesList[$key];
            $contract->setProperty($property);
            $contract->setStatus(ContractStatus::PENDING);
            $contract->setSearch('aaa');
            $contract->setUnit(null);
            $em->persist($contract);

        }

        $em->flush();
        static::$kernel = null;
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new PropertyCommand());

        $command = $application->find('property:duplicate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'               => $command->getName(),
                '--only-with-contract'  => 1,
            )
        );

        $result = explode('||', $commandTester->getDisplay());
        $this->assertEquals(23, count($result));
        $this->assertRegExp('/3850 Coleman Rd, East Lansing, MI 33333 /', $result[0]);
    }
}
