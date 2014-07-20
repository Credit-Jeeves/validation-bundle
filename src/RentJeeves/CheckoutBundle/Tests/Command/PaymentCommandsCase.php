<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\TestBundle\EventListener\EmailListener;
use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentCommandsCase extends BaseTestCase
{
    /**
     * @var EmailListener
     */
    protected $plugin;

    protected function setUp()
    {
        $this->load(true);
        $this->plugin = $this->registerEmailListener();
        $this->plugin->clean();
    }

    protected function executePayCommand($jobId)
    {
        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $command = $application->find('payment:pay');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--jms-job-id' => $jobId,
            )
        );
        return $commandTester;
    }


    /**
     * @test
     */
    public function collectAndPay()
    {
        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(1, $jobs);

        $commandTester = $this->executePayCommand($jobs[0]->getId());

        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());
        $this->assertCount(1, $this->plugin->getPreSendMessages());
        $this->assertEquals('Your Rent is Processing', $this->plugin->getPreSendMessage(0)->getSubject());

        $this->plugin->clean();

        $commandTester = $this->executePayCommand($jobs[0]->getId());
        $this->assertRegExp("/Start\nPayment already executed./", $commandTester->getDisplay());
        $this->assertCount(0, $this->plugin->getPreSendMessages());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(0, $jobs);
    }

    /**
     * @test
     */
    public function collectCreditTrackAndPay()
    {
        $jobs = $this->getContainer()->get('doctrine')
            ->getRepository('RjDataBundle:PaymentAccount')
            ->collectCreditTrackToJobs();
        $this->assertCount(1, $jobs);

        $commandTester = $this->executePayCommand($jobs[0]->getId());

        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());

        $this->assertCount(1, $this->plugin->getPreSendMessages());
        $this->assertEquals('Receipt from Rent Track', $this->plugin->getPreSendMessage(0)->getSubject());
    }
}
