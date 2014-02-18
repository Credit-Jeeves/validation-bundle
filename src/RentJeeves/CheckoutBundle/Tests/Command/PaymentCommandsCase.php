<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentCommandsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function collectAndPay()
    {
        $this->load(true);
        static::$kernel = null;
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(1, $jobs);

        $command = $application->find('payment:pay');
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--jms-job-id' => $jobs[0]->getId(),
            )
        );
        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());
        // No Emails, current status of order is PENDING Email will be send on COMPLETE status
        $this->assertCount(0, $plugin->getPreSendMessages());

        $plugin->clean();
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--jms-job-id' => $jobs[0]->getId(),
            )
        );
        $this->assertRegExp("/Start\nPayment already executed./", $commandTester->getDisplay());
        $this->assertCount(0, $plugin->getPreSendMessages());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(0, $jobs);
    }
}
