<?php

namespace RentJeeves\ExperianBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\ComponentBundle\Command\GetScoreTrackReportCommand;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\TestBundle\Command\BaseTestCase;

class GetScoreTrackReportCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function executeCommand()
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Tenant $user */
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('marion@rentrack.com');
        $report = new ReportPrequal();
        $report->setUser($user);
        $report->setRawData('');
        $job = new Job();
        $job->addRelatedEntity($report);
        // should create new order and operation b/c we take report from it after it was paid
        $order = new OrderSubmerchant();
        $operation = new Operation();
        $operation->setType(OperationType::REPORT);
        $operation->setReport($report);
        $operation->setOrder($order);
        $operation->setPaidFor(new \DateTime());
        $order->addOperation($operation);
        $order->setUser($user);
        $order->setSum(1);
        $order->setStatus(OrderStatus::COMPLETE);
        $order->setPaymentType(OrderPaymentType::CASH);

        $em->persist($report);
        $em->persist($job);
        $em->persist($order);
        $em->persist($operation);
        $em->flush();

        $scores = $em->getRepository('DataBundle:Score')->findByUser($user);
        $this->assertCount(
            1,
            $scores,
            sprintf('Check fixtures for user with email "%s" should be one score record', $user->getEmail())
        );

        $application = new Application($this->getKernel());
        $application->add(new GetScoreTrackReportCommand());

        $command = $application->find('score-track:get-report');
        $commandTester = new CommandTester($command);
        $this->assertEquals(
            0,
            $commandTester->execute([
                'command' => $command->getName(),
                '--jms-job-id' => $job->getId()
            ]),
            'Command should be finished without error with exit code "0"'
        );

        $em->clear();
        /** @var ReportPrequal $report */
        $report = $em->getRepository('DataBundle:ReportPrequal')->findOneById($report->getId());
        $this->assertNotEmpty($report->getRawData(), 'Command should load and save report data.');
        $scores = $em->getRepository('DataBundle:Score')->findByUser($user);
        $this->assertCount(2, $scores, 'Command should add new score record');
    }
}
