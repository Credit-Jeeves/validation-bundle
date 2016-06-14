<?php

namespace RentJeeves\ComponentBundle\Tests\Functional\Command;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
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
    public function updateExistingReportByCommand()
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
        /** @var OrderCreationManager $orderCretionManager */
        $orderCretionManager = $this->getContainer()->get('payment_processor.order_creation_manager');
        $order = $orderCretionManager->createCreditTrackOrder($user->getPaymentAccounts()->last());
        $order->getOperations()->last()->setReport($report);
        $order->setStatus(OrderStatus::COMPLETE);

        $em->persist($report);
        $em->persist($job);
        $em->persist($order);
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

    /**
     * @test
     */
    public function createNewReportForFreeCommand()
    {
        $this->load(true);

        $em = $this->getEntityManager();
        /** @var Tenant $user */
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('marion@rentrack.com');
        $report = new ReportPrequal();
        $report->setUser($user);
        $report->setRawData('');
        $user->getSettings()->setScoretrackFreeUntil(new \DateTime('+1 month'));
        $job = new Job();
        $job->addRelatedEntity($report);
        $em->persist($report);
        $em->persist($job);
        $em->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

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
        $report = $em->getRepository('DataBundle:ReportPrequal')->find($report->getId());
        $this->assertNotEmpty($report->getRawData(), 'Command should load and save report data.');
        $scores = $em->getRepository('DataBundle:Score')->findByUser($user);
        $this->assertCount(2, $scores, 'Command should add new score record');

        $this->assertCount(1, $plugin->getPreSendMessages());
        $this->assertEquals(
            'ScoreTrack Updated',
            $plugin->getPreSendMessage(0)->getSubject(),
            'Check email for score track free'
        );
    }
}
