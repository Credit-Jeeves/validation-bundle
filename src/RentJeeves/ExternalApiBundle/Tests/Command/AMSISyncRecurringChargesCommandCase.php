<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Command\AMSISyncRecurringChargesCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\AMSI\AMSIClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AMSISyncRecurringChargesCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncContractRentForAMSI()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        $repo = $em->getRepository('RjDataBundle:Contract');
        $contract = $repo->find(20);
        $this->assertNotNull($contract);
        $this->assertEquals(0, $contract->getIntegratedBalance());
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);
        $contract->getHolding()->setUseRecurringCharges(true);
        $contract->setRent(123321); // test value

        $propertyMapping = $contract->getProperty()->getPropertyMappingByHolding($contract->getHolding());
        $propertyMapping->setExternalPropertyId(AMSIClientCase::EXTERNAL_PROPERTY_ID);
        $unit = $contract->getUnit();
        $unitExternalMapping = new UnitMapping();
        $unitExternalMapping->setExternalUnitId('001|01|101');
        $unitExternalMapping->setUnit($unit);
        $unit->setUnitMapping($unitExternalMapping);
        $tenant = $contract->getTenant();
        $residentMapping = $tenant->getResidentForHolding($contract->getHolding());
        $residentMapping->setResidentId('296455');

        $em->flush();

        $application = new Application($this->getKernel());
        $application->add(new AMSISyncRecurringChargesCommand());

        $command = $application->find('api:accounting:amsi:sync-recurring-charges');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->assertContains(
            'AMSI sync Recurring Charge: Rent for contracts (20) updated',
            $commandTester->getDisplay(),
            'Rent for Contract#20 not updated'
        );
    }
}
