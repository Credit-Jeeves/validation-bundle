<?php
namespace RentJeeves\ExternalApiBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use RentJeeves\ExternalApiBundle\Command\MigrateResManCommand;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\Output;
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
            if ($propertyMapping->getProperty()->getId() == 1) {
                $propertyMapping->setExternalPropertyId('b342e58c-f5ba-4c63-b050-cf44439bb37d');
                continue;
            }

            // we can't have two properties with the same external id, so remove the mapping for the
            // second one created by our fixtures.
            $em->remove($propertyMapping);
        }
        $holding->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $unitMapping->setExternalUnitId('118');
        $em->flush();
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new MigrateResManCommand());

        $command = $application->find('external-api:resman:migrate-external-units');
        $this->ExecuteConsoleCommand($command);
        $em->refresh($unitMapping);
        $this->assertEquals('b342e58c-f5ba-4c63-b050-cf44439bb37d|1|118', $unitMapping->getExternalUnitId());
    }

    /**
     * @param $command
     */
    protected function executeConsoleCommand($command)
    {
        /*
         * To enable debug logging set PHPUNITDEBUG environment variable before running tests.
         * For example:
         *
         *   export PHPUNITDEBUG=yesplease ; phpunit MyCoolTestCase.php
         */
        $debugMode = (getenv('PHPUNITDEBUG') !== false);
        $verbosityLevel = ($debugMode) ? Output::VERBOSITY_NORMAL : Output::VERBOSITY_DEBUG;

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ],
            [
                'verbosity' => $verbosityLevel
            ]
        );

        if ($debugMode) {
            echo $commandTester->getDisplay();
        }
    }
}
