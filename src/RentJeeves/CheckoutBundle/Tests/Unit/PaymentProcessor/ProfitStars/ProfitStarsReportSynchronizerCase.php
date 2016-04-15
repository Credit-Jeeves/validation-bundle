<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\ProfitStarsReportSynchronizer;
use RentJeeves\DataBundle\Entity\ProfitStarsTransaction;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\ArrayOfWSEventReport;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\GetHistoricalEventReportResponse;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\TransactionReportingClient;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;
use RentTrack\ProfitStarsClientBundle\TransactionReporting\Model\WSEventReport;

class ProfitStarsReportSynchronizerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldUpdateProfitStarsTransactionForSettledEvent()
    {
        $order = new Order();
        $order->setProfitStarsTransaction(new ProfitStarsTransaction());

        $transaction = new Transaction();
        $transaction->setOrder($order);

        $repositoryMock = $this->getTransactionRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->willReturn($transaction);

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with('RjDataBundle:Transaction')
            ->willReturn($repositoryMock);

        $reportingClient = $this->getTransactionReportingClientMock();
        $reportingClient->expects($this->once())
            ->method('GetHistoricalEventReport')
            ->willReturn($this->getSettledReport());

        $reportSynchronizer = new ProfitStarsReportSynchronizer(
            $reportingClient,
            $this->getOrderStatusManagerMock(),
            $emMock,
            $this->getLoggerMock(),
            'test',
            'test'
        );

        $reportSynchronizer->sync('location', 'entityId', new \DateTime(), new \DateTime());

        $this->assertEquals(
            '{54a9a112-d59b-44a6-b3ad-ec7e5d078648}',
            $order->getProfitStarsTransaction()->getTransactionNumber(),
            'TransactionNumber is not updated.'
        );
    }

    /**
     * @test
     */
    public function shouldCreateReversedTransactionForReversalEventAndMoveOrderToReturnedStatus()
    {
        $order = new Order();
        $order->setStatus(OrderStatus::COMPLETE);
        $transaction = new Transaction();
        $transaction->setOrder($order);

        $repositoryMock = $this->getTransactionRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('findOneCompletedByProfitStarsTransactionId')
            ->with($this->equalTo('{37e9b6b9-4058-4ac6-aa76-51f9bb67badc}'))
            ->willReturn($transaction);

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with('RjDataBundle:Transaction')
            ->willReturn($repositoryMock);
        $emMock->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($subject) use ($order) {
                /** @var Transaction $subject */
                $this->assertInstanceOf(Transaction::class, $subject, 'Unexpected object for persist.');
                $this->assertEquals($order, $subject->getOrder(), 'Incorrect Order for Transaction.');
                $this->assertEquals(
                    'ZMC825CFBA2', // ReferenceNumber
                    $subject->getTransactionId(),
                    'Incorrect TransactionId for Transaction.'
                );

                return true;
            }));

        $reportingClient = $this->getTransactionReportingClientMock();
        $reportingClient->expects($this->once())
            ->method('GetHistoricalEventReport')
            ->willReturn($this->getReversalReport('Declined'));

        $orderStatusManager = $this->getOrderStatusManagerMock();
        $orderStatusManager->expects($this->once())
            ->method('setReturned');

        $reportSynchronizer = new ProfitStarsReportSynchronizer(
            $reportingClient,
            $orderStatusManager,
            $emMock,
            $this->getLoggerMock(),
            'test',
            'test'
        );

        $reportSynchronizer->sync('location', 'entityId', new \DateTime(), new \DateTime());
    }

    /**
     * @test
     */
    public function shouldSendAlertForSuspendedStatus()
    {
        $reportingClient = $this->getTransactionReportingClientMock();
        $reportingClient->expects($this->once())
            ->method('GetHistoricalEventReport')
            ->willReturn($this->getReversalReport('Suspended'));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->never())
            ->method('persist');
        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('alert');
        $reportSynchronizer = new ProfitStarsReportSynchronizer(
            $reportingClient,
            $this->getOrderStatusManagerMock(),
            $emMock,
            $logger,
            'test',
            'test'
        );

        $reportSynchronizer->sync('location', 'entityId', new \DateTime(), new \DateTime());
    }

    /**
     * @test
     */
    public function shouldSkipReportForResolvedStatus()
    {
        $reportingClient = $this->getTransactionReportingClientMock();
        $reportingClient->expects($this->once())
            ->method('GetHistoricalEventReport')
            ->willReturn($this->getReversalReport('Resolved'));

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->never())
            ->method('persist');
        $logger = $this->getLoggerMock();
        $logger->expects($this->at(1))
            ->method('debug')
            ->with($this->stringContains('Skip report'));
        $reportSynchronizer = new ProfitStarsReportSynchronizer(
            $reportingClient,
            $this->getOrderStatusManagerMock(),
            $emMock,
            $logger,
            'test',
            'test'
        );

        $reportSynchronizer->sync('location', 'entityId', new \DateTime(), new \DateTime());
    }

    /**
     * @return GetHistoricalEventReportResponse
     */
    protected function getSettledReport()
    {
        $report = new WSEventReport();
        $report->setEventDateTime('2015-11-25T00:00:00');
        $report->setEventType('Settled');
        $report->setEventDatastring('');
        $report->setTransactionStatus('Disputed');
        $report->setPaymentType('Checking');
        $report->setNameOnAccount('Charles Bridges');
        $report->setTransactionNumber('{54a9a112-d59b-44a6-b3ad-ec7e5d078648}');
        $report->setReferenceNumber('7VC825CFBA2');
        $report->setCustomerNumber('218');
        $report->setOperationType('Sale');
        $report->setLocationName('Location 1');
        $report->setTransactionDateTime('0001-01-01T00:00:00');
        $report->setTotalAmount('195.0000');
        $report->setOwnerAppReferenceId(0);

        $result = new ArrayOfWSEventReport();
        $result->setWSEventReport([$report]);

        $response = new GetHistoricalEventReportResponse();
        $response->setGetHistoricalEventReportResult($result);

        return $response;
    }

    /**
     * @return GetHistoricalEventReportResponse
     */
    protected function getReversalReport($status)
    {
        $report = new WSEventReport();
        $report->setEventDateTime('2015-11-24T16:35:48.773');
        $report->setEventType($status);
        $report->setEventDatastring('Velocity Exceeded');
        $report->setTransactionStatus('Declined');
        $report->setPaymentType('Checking');
        $report->setNameOnAccount('Patricia Rothwell');
        $report->setTransactionNumber('{37e9b6b9-4058-4ac6-aa76-51f9bb67badc}');
        $report->setReferenceNumber('ZMC825CFBA2');
        $report->setCustomerNumber('198');
        $report->setOperationType('Sale');
        $report->setLocationName('Location 1');
        $report->setTransactionDateTime('0001-01-01T00:00:00');
        $report->setTotalAmount('950.0000');
        $report->setOwnerAppReferenceId(0);

        $result = new ArrayOfWSEventReport();
        $result->setWSEventReport([$report]);

        $response = new GetHistoricalEventReportResponse();
        $response->setGetHistoricalEventReportResult($result);

        return $response;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|OrderStatusManager
     */
    protected function getOrderStatusManagerMock()
    {
        return $this->getBaseMock(OrderStatusManager::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TransactionReportingClient
     */
    protected function getTransactionReportingClientMock()
    {
        return $this->getBaseMock(TransactionReportingClient::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TransactionRepository
     */
    protected function getTransactionRepositoryMock()
    {
        return $this->getBaseMock(TransactionRepository::class);
    }
}
