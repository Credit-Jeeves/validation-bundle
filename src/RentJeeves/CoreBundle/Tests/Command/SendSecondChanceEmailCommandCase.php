<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\SendSecondChanceEmailCommand;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class SendSecondChanceEmailCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSendEmailForContractWithNeededParams()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $newTenant = new Tenant();
        $newTenant->setUsername('test@test.test');
        $newTenant->setEmail('test@test.test');
        $newTenant->setPassword('test');
        $newTenant->setFirstName('test');
        $this->getEntityManager()->persist($newTenant);

        $contract = $this->getEntityManager()->find('RjDataBundle:Contract', 1);

        $contract->setCreatedAt(new \DateTime('-1 month'));
        $contract->setFinishAt(new \DateTime('+5 month'));
        $contract->setStatus(ContractStatus::INVITE);
        $contract->setTenant($newTenant);

        $this->getEntityManager()->flush();

        $application = new Application($this->getKernel());
        $application->add(new SendSecondChanceEmailCommand());

        $command = $application->find('email:second_chance');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
            ]
        );

        $this->assertCount(1, $plugin->getPreSendMessages(), 'Message not sent.');
        $this->assertEquals('Renttrack', $plugin->getPreSendMessage(0)->getSubject(), 'Sent not correct message.');
    }
}
