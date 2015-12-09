<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\MoveContractCommand;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class MoveContractCommandCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Contract with id = 0 not found.
     */
    public function shouldThrowExceptionIfSendNotCorrectContactId()
    {
        $this->createAndExecuteCommandTester(['--contract_id' => 0]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unit with id = 0 not found.
     */
    public function shouldThrowExceptionIfSendNotCorrectUnitId()
    {
        $this->createAndExecuteCommandTester(['--contract_id' => 1, '--dst_unit_id' => 0]);
    }

    /**
     * @test
     */
    public function shouldUpdateContractIfSendValidData()
    {
        $this->load(true);
        // Prepare data
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(17);
        $unit = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(1);

        $this->getEntityManager()->flush();

        $contract->getUnit()->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $unit->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);

        $this->createAndExecuteCommandTester(
            [
                '--contract_id' => $contract->getId(),
                '--dst_unit_id' => $unit->getId()
            ]
        );

        $this->getEntityManager()->refresh($contract);

        $this->assertEquals(
            $unit->getId(),
            $contract->getUnit()->getId(),
            'Contract`s Unit is not updated'
        );
        $this->assertEquals(
            $unit->getProperty()->getId(),
            $contract->getUnit()->getProperty()->getId(),
            'Contract`s Property is not updated'
        );
        $this->assertEquals(
            $unit->getGroup()->getId(),
            $contract->getUnit()->getGroup()->getId(),
            'Contract`s Group is not updated'
        );
        $this->assertEquals(
            $unit->getHolding()->getId(),
            $contract->getUnit()->getHolding()->getId(),
            'Contract`s Holding is not updated'
        );
    }

    /**
     * @test
     */
    public function shouldNotUpdateContractIfSendValidDataAndEnableDryRunMode()
    {
        $this->load(true);
        // Prepare data
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(17);
        $unit = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(1);
        $activePayment = $this->getEntityManager()->getRepository('RjDataBundle:Payment')->find(1);
        $activePayment->setContract($contract);

        $this->getEntityManager()->flush();

        $contract->getUnit()->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);
        $unit->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);

        $this->createAndExecuteCommandTester(
            [
                '--contract_id' => $contract->getId(),
                '--dst_unit_id' => $unit->getId(),
                '--dry-run' => 1
            ]
        );

        $this->getEntityManager()->refresh($contract);

        $this->assertNotEquals(
            $unit->getId(),
            $contract->getUnit()->getId(),
            'Contract`s Unit is updated'
        );
    }

    /**
     * @param array $params
     */
    private function createAndExecuteCommandTester(array $params = [])
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new MoveContractCommand());

        $command = $application->find('renttrack:contract:move');
        $commandTester = new CommandTester($command);

        $params['command'] = $command->getName();

        $commandTester->execute($params);
    }
}
