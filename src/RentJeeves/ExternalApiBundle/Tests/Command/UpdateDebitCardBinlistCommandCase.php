<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\ExternalApiBundle\Command\UpdateDebitCardBinlistCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateDebitCardBinlistCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldRemoveOldDataAndimportNew()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        $debitCardBinlist = new DebitCardBinlist();
        $debitCardBinlist->setIin(11112231);
        $em->persist($debitCardBinlist);
        $em->flush();

        $application = new Application($this->getKernel());
        $application->add(new UpdateDebitCardBinlistCommand());

        $command = $application->find('api:binlist:update_data');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $result = $em->getRepository('RjDataBundle:DebitCardBinlist')->findBy(['iin' => 11112231]);
        $this->assertEmpty($result, 'We should remove old data');
        $this->assertGreaterThan(
            6000, //Currently we have 6801, so lets check 6000
            count($em->getRepository('RjDataBundle:DebitCardBinlist')->findAll())
        );
    }
}
