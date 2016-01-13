<?php
namespace RentJeeves\TestBundle\Command;

use CreditJeeves\TestBundle\Command\BaseTestCase as Base;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class BaseTestCase extends Base
{
    const APP = 'AppRj';

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @param Command $command instance of CommandClass that you are testing
     * @param array $params
     *
     * @return string
     */
    protected function executeCommandTester(Command $command, array $params = [])
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $application->add($command);

        $command = $application->find($command->getName());
        $this->commandTester = new CommandTester($command);

        $params['command'] = $command->getName();

        return $this->commandTester->execute($params);
    }
}
