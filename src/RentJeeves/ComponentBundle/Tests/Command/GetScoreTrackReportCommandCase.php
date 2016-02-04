<?php

namespace RentJeeves\ComponentBundle\Tests\Command;

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
     * @return array
     */
    public function providerExecute()
    {
        return [
            ['-1 month', 0],
            ['+1 month', 1],
        ];
    }

    /**
     * @param string $scoreTrackFreeMonth
     * @param string $emailCount
     *
     * @test
     * @dataProvider providerExecute
     */
    public function executeCommand($scoreTrackFreeMonth, $emailCount)
    {
        $scoreTrackFreeDate = new \DateTime($scoreTrackFreeMonth);
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var Tenant $user */
        $user = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('marion@rentrack.com');
        $user->getSettings()->setScoreTrackFreeUntil($scoreTrackFreeDate);
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
        /** @var ReportPrequal $report */
        $report = $em->getRepository('DataBundle:ReportPrequal')->findOneById($report->getId());
        $this->assertNotEmpty($report->getRawData(), 'Command should load and save report data.');
        $scores = $em->getRepository('DataBundle:Score')->findByUser($user);
        $this->assertCount(2, $scores, 'Command should add new score record');

        $this->assertCount($emailCount, $plugin->getPreSendMessages());
    }
}
