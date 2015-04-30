<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\ExternalApiBundle\Command\YardiVersionNumberCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class YardiVersionNumberCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executeCommand()
    {
        $this->load(true);
        $application = new Application($this->getKernel());
        $application->add(new YardiVersionNumberCommand());

        $command = $application->find('api:yardi:version');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $this->assertRegExp(
            '/Current version for holding Rent Holding is 7Sv.*/',
            trim($commandTester->getDisplay())
        );
    }
}
