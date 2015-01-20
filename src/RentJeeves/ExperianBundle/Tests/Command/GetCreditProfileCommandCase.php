<?php

namespace RentJeeves\ExperianBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\ReportD2c;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\ExperianBundle\Command\GetCreditProfileCommand;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PaymentReportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\DataBundle\Entity\Heartland;

class GetCreditProfileCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executeCommand()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $user = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('marion@rentrack.com');
        $report = new ReportD2c();
        $report->setUser($user);
        $report->setRawData('');
        $job = new Job();
        $job->addRelatedEntity($report);
        $em->persist($report);
        $em->persist($job);
        $em->flush();


        $scores = $em->getRepository('DataBundle:Score')->findByUser($user);
        $this->assertCount(0, $scores);

        $application = new Application($this->getKernel());
        $application->add(new GetCreditProfileCommand());

        $command = $application->find('experian-credit_profile:get');
        $commandTester = new CommandTester($command);
        $this->assertEquals(
            0,
            $commandTester->execute(
                array(
                    'command' => $command->getName(),
                    '--jms-job-id' => $job->getId()
                )
            )
        );

        static::$kernel = null;

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $report = $em->getRepository('DataBundle:ReportD2c')->findOneById($report->getId());
        $this->assertNotEmpty($report->getRawData());
        $scores = $em->getRepository('DataBundle:Score')->findByUser($user);
        $this->assertCount(1, $scores);
    }
}
