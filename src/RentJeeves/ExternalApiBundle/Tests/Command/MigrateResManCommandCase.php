<?php
namespace RentJeeves\ExternalApiBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManager;
use RentJeeves\ExternalApiBundle\Command\MigrateResManCommand;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class MigrateResManCommandCase extends BaseTestCase
{
    /**
      * @test
     */
    public function shouldMigrate()
    {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            [
                'externalUnitId' => 'AAABBB-7'
            ]
        );
        $this->assertNotEmpty($unitMapping);
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        /** @var PropertyMapping $propertyMapping */
        foreach ($holding->getPropertyMapping() as $propertyMapping) {
            $propertyMapping->setExternalPropertyId('b342e58c-f5ba-4c63-b050-cf44439bb37d');
        }
        $holding->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $unitMapping->setExternalUnitId('118');
        $em->flush();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new MigrateResManCommand());

        $command = $application->find('external-api:resman:migrate-external-units');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ]
        );
        $em->refresh($unitMapping);
        $this->assertEquals('b342e58c-f5ba-4c63-b050-cf44439bb37d|1|118', $unitMapping->getExternalUnitId());
    }
}
