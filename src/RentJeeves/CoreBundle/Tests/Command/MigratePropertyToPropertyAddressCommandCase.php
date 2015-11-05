<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\MigratePropertyToPropertyAddressCommand;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MigratePropertyToPropertyAddressCommandCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldCreateJobForMigrateIfOptionPageNotSend()
    {
        $this->load(true);
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findOneById(1);
        $property->setIndex('test');
        $this->writeAttribute($property,'propertyAddress', null);

        $this->getEntityManager()->flush();

        $allJobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $count = count($allJobs);

        $application = new Application($this->getKernel());
        $application->add(new MigratePropertyToPropertyAddressCommand());

        $command = $application->find('property:migrate_to_property_address');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $allJobsAfterCommand = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $countAfterCommand = count($allJobsAfterCommand);
        $this->assertEquals($count + 1, $countAfterCommand, 'New job is not added');
    }

    /**
     * @test
     */
    public function shouldCreateNewPropertyAddressIfPropertyHasNotExistIndexAndOptionPageSend()
    {
        $this->load(true);
        /**
         * @var Property $property
         */
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findOneById(1);
        $property->setIndex('test');
        $property->setNumber('test');
        $property->setStreet('test');
        $property->setCity('test');
        $property->setArea('test');
        $property->setZip('test');
        $this->writeAttribute($property,'propertyAddress', null);

        $this->getEntityManager()->flush();

        $allPropertyAddress = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->findAll();
        $count = count($allPropertyAddress);

        $application = new Application($this->getKernel());
        $application->add(new MigratePropertyToPropertyAddressCommand());

        $command = $application->find('property:migrate_to_property_address');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--page' => 1
            ]
        );

        $allAfterCommand = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->findAll();
        $countAfterCommand = count($allAfterCommand);
        $this->assertEquals($count + 1, $countAfterCommand, 'New PropertyAddress is not added');
    }

    /**
     * @test
     */
    public function shouldRelatePropertyAndPropertyAddressIfPropertyHasExistIndexAndOptionPageSend()
    {
        $this->load(true);
        /**
         * @var Property $property
         */
        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findOneById(1);
        $propertyAddress = $property->getPropertyAddress();
        $property->setIndex($propertyAddress->getIndex());
        $this->writeAttribute($property,'propertyAddress', null);

        $this->getEntityManager()->flush();

        $allPropertyAddress = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->findAll();
        $count = count($allPropertyAddress);

        $application = new Application($this->getKernel());
        $application->add(new MigratePropertyToPropertyAddressCommand());

        $command = $application->find('property:migrate_to_property_address');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--page' => 1
            ]
        );

        $allAfterCommand = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->findAll();
        $countAfterCommand = count($allAfterCommand);
        $this->assertEquals($count, $countAfterCommand, 'New PropertyAddress is not added');

        $this->getEntityManager()->refresh($property);

        $this->assertEquals(
            $property->getPropertyAddress(),
            $propertyAddress,
            'Incorrect relation with PropertyAddress'
        );
    }
}
