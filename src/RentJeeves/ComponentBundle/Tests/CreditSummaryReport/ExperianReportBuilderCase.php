<?php

namespace RentJeeves\ComponentBundle\Tests\PidKiqProcessor;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderCreationManager\OrderCreationManager;
use RentJeeves\ComponentBundle\CreditSummaryReport\ExperianReportBuilder;
use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ExperianReportBuilderCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;
    /**
     * @var ExperianReportBuilder
     */
    protected $experianReportBuilder;

    /**
     * @var User
     */
    protected $user;

    protected function load($reload = false)
    {
        parent::load($reload);
        $this->setUp(); // this need because we have some problems with doctrine after reload fixtures
    }

    public function setUp()
    {
        $this->experianReportBuilder = new ExperianReportBuilder(
            $this->getEntityManager(),
            $this->getLoggerMock()
        );
        $this->experianReportBuilder->setCreditProfile(
            $this->getContainer()->get('experian.net_connect.credit_profile')
        );

        $this->user = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Doesn't have report for update
     */
    public function shouldThrowExceptionWhenTryUpdateNotExistReport()
    {
        $this->load(true);

        $this->experianReportBuilder->build($this->user, true);
    }

    /**
     * @test
     */
    public function shouldCreateNewReportPrequal()
    {
        $this->assertCount(
            0,
            $this->user->getReportsPrequal(),
            sprintf('Check fixtures user with email "%s" should not have prequal reports', $this->user->getEmail())
        );
        $this->assertCount(
            0,
            $this->user->getScores(),
            sprintf('Check fixtures user with email "%s" should not have any score records', $this->user->getEmail())
        );

        $this->experianReportBuilder->build($this->user);

        $this->getEntityManager()->refresh($this->user);
        $this->assertCount(
            1,
            $this->user->getReportsPrequal(),
            'Should was created new prequal report'
        );
        /** @var ReportPrequal $newReport */
        $newReport = $this->user->getReportsPrequal()->last();
        $this->assertNotEmpty($newReport->getRawData(), 'Should was loaded report data');

        $this->assertCount(
            1,
            $this->user->getScores(),
            'Should was created new score record'
        );
        /** @var Score $newScoreRecord */
        $newScoreRecord = $this->user->getScores()->last();
        $this->assertNotEmpty($newScoreRecord->getScore(), 'Score value should not be empty');
    }

    /**
     * @test
     */
    public function shouldUpdateExistReportPrequal()
    {
        $oldScoreRecordsCount = $this->user->getScores()->count();
        $em = $this->getEntityManager();
        $report = new ReportPrequal();
        $report->setUser($this->user);
        $report->setRawData('');
        // should create new order and operation b/c we take report from it after it was paid
        /** @var OrderCreationManager $orderCretionManager */
        $orderCretionManager = $this->getContainer()->get('payment_processor.order_creation_manager');
        $order = $orderCretionManager->createCreditTrackOrder($this->user->getPaymentAccounts()->last());
        $order->getOperations()->last()->setReport($report);
        $order->setStatus(OrderStatus::COMPLETE);

        $em->persist($report);
        $em->persist($order);
        $em->flush();

        $this->experianReportBuilder->build($this->user, true);

        $this->getEntityManager()->refresh($this->user);
        /** @var ReportPrequal $newReport */
        $newReport = $this->user->getReportsPrequal()->last();
        $this->assertNotEmpty($newReport->getRawData(), 'Should was loaded report data');

        $this->assertCount(
            $oldScoreRecordsCount + 1,
            $this->user->getScores(),
            'Should was created new score record'
        );
        /** @var Score $newScoreRecord */
        $newScoreRecord = $this->user->getScores()->last();
        $this->assertNotEmpty($newScoreRecord->getScore(), 'Score value should not be empty');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Report have been already updated
     * @extends shouldUpdateExistReportPrequal
     */
    public function shouldThrowExceptionWhenTryUpdateNotEmptyExistReport()
    {
        $this->experianReportBuilder->build($this->user, true);
    }
}
