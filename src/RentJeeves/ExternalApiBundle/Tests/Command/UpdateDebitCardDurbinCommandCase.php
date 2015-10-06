<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\ExternalApiBundle\Command\UpdateDebitCardDurbinCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateDebitCardDurbinCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldRemoveOldDataAndImportNew()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $this->assertCount(
            0,
            $em->getRepository('RjDataBundle:DebitCardDurbin')->findAll(),
            'We should check out fixtures'
        );

        $application = new Application($this->getKernel());
        $application->add(new UpdateDebitCardDurbinCommand());

        $command = $application->find('api:durbin:update-data');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
        $this->assertCount(
            6,
            $em->getRepository('RjDataBundle:DebitCardDurbin')->findAll(),
            'We should import csv file'
        );
    }
}
