<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\Job;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class CommandsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executePay()
    {
        $this->load(true);
        static::$kernel = null;
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();

        $command = $application->find('payment:pay');
        $commandTester = new CommandTester($command);
        /** @var Job $job */
        foreach ($jobs as $job) {
            $commandTester->execute(
                array(
                    'command' => $command->getName(),
                    '--jms-job-id' => $job->getId(),
                )
            );
        }
        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());
        $this->assertCount(1, $plugin->getPreSendMessages());
    }

    /**
     * @test
     * @depends execute
     */
    public function executeRepeat()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add(new PayCommand());
        
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $command = $application->find('Payment:process');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
            )
        );
        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(2, $count);
        $this->assertRegExp('/Start payment process(.*)OK/', $commandTester->getDisplay());
    }
}
