<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Command\ResManSyncRentCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ResManSyncRentCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncContractRent()
    {
        $this->load(true);

        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(20);
        $contract->setRent(123321);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::RESMAN);
        $contract->getHolding()->setUseRecurringCharges(true);
        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $contract->getUnit()->setName(ResManClientCase::RESMAN_UNIT_ID);
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId('4366dd6b-0b89-47e2-9624-b91df64b71a4');
        $this->getEntityManager()->flush();

        $application = new Application($this->getKernel());
        $application->add(new ResManSyncRentCommand());

        $command = $application->find('api:resman:sync-rent');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains(
            'ResMan sync Recurring Charge: Rent for Contract#20 updated',
            $commandTester->getDisplay(),
            'Rent for Contract#20 not updated'
        );
    }
}
