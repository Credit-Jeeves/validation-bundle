<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Report;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectDepositReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReportSynchronizer;
use RentJeeves\DataBundle\Entity\OutboundTransaction;
use RentJeeves\DataBundle\Enum\OutboundTransactionType;
use RentJeeves\TestBundle\BaseTestCase;

class ReportSynchronizerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateObjectReportSynchronizer()
    {
        new ReportSynchronizer($this->getEntityManager(), $this->getLoggerMock());
    }

    /**
     * @test
     */
    public function shouldUpdateOrderAndDepositDateForDepositTransactionIfDateIsNull()
    {
        $this->load(true);

        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );
        $order = $outboundTransaction->getOrder();
        $order->setStatus(OrderStatus::SENDING);
        $this->getEntityManager()->flush($order);

        $this->assertNull($outboundTransaction->getDepositDate());

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectDepositReportTransaction();
        $date = new \DateTime();
        $transaction->setDepositDate($date);
        $transaction->setTransactionId($outboundTransaction->getTransactionId());

        $report->addTransaction($transaction);

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $this->getLoggerMock());
        $synchronizer->synchronize($report);

        $this->getEntityManager()->refresh($outboundTransaction);
        $this->assertEquals($date, $outboundTransaction->getDepositDate());
        $this->assertEquals(OrderStatus::COMPLETE, $outboundTransaction->getOrder()->getStatus());
    }

    /**
     * @test
     */
    public function shouldLogAlertForDepositTransactionIfOutboundTransactionNotFound()
    {
        $report = new PaymentProcessorReport();
        $transaction = new PayDirectDepositReportTransaction();
        $date = new \DateTime();
        $transaction->setDepositDate($date);
        $transaction->setTransactionId(1000);

        $report->addTransaction($transaction);

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('alert')
            ->with('Deposit Outbound Transaction #1000 not found');

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $loggerMock);
        $synchronizer->synchronize($report);
    }

    /**
     * @test
     */
    public function shouldLogAlertForDepositTransactionIfDepositDateIsNotNull()
    {
        $this->load(true);

        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 2]
        );
        $this->assertNotNull($outboundTransaction->getDepositDate());

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectDepositReportTransaction();
        $date = new \DateTime();
        $transaction->setDepositDate($date);
        $transaction->setTransactionId($outboundTransaction->getTransactionId());

        $report->addTransaction($transaction);

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('alert')
            ->with('PayDirect Deposit Transaction #2 already has deposit date. Skipping.');

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $loggerMock);
        $synchronizer->synchronize($report);
    }

    /**
     * @test
     */
    public function shouldLogAlertForDepositTransactionIfOrderStatusIsNotSending()
    {
        $this->load(true);

        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );
        $order = $outboundTransaction->getOrder();
        $this->assertNotEquals(OrderStatus::SENDING, $order->getStatus());

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectDepositReportTransaction();
        $transaction->setTransactionId($outboundTransaction->getTransactionId());

        $report->addTransaction($transaction);

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('alert')
            ->with('Status for Order #2 must be \'sending\', \'complete\' given');

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $loggerMock);
        $synchronizer->synchronize($report);
    }

    /**
     * @test
     */
    public function shouldLogEmergencyForResponseTransactionIfStatusIsNotCorrect()
    {
        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectResponseReportTransaction();
        $transaction->setResponseCode('WRONG_CODE');
        $transaction->setTransactionId($outboundTransaction->getTransactionId());

        $report->addTransaction($transaction);

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('emergency')
            ->with($this->stringContains('ERRORCODE value different from the expected value.'));

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $loggerMock);
        $synchronizer->synchronize($report);
    }

    /**
     * @test
     */
    public function shouldUpdateTransactionForResponseTransactionIfStatusIsCorrect()
    {
        $this->load(true);

        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );
        $this->assertNull($outboundTransaction->getBatchId());
        $this->assertNull($outboundTransaction->getBatchCloseDate());

        $date = new \DateTime();

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectResponseReportTransaction();
        $transaction->setResponseCode('READY TO DISBURSE');
        $transaction->setTransactionId($outboundTransaction->getTransactionId());
        $transaction->setBatchId(123456789);
        $transaction->setBatchCloseDate($date);

        $report->addTransaction($transaction);

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $this->getLoggerMock());
        $synchronizer->synchronize($report);

        $this->getEntityManager()->refresh($outboundTransaction);

        $this->assertEquals(123456789, $outboundTransaction->getBatchId());
        $this->assertEquals($date, $outboundTransaction->getBatchCloseDate());
    }

    /**
     * @test
     */
    public function shouldLogAlertForReversalTransactionIfOrderStatusIsNotCorrect()
    {
        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectReversalReportTransaction();
        $transaction->setTransactionId($outboundTransaction->getTransactionId());
        $transaction->setTransactionType(PayDirectReversalReportTransaction::TYPE_REFUNDING);

        $report->addTransaction($transaction);

        $order = $outboundTransaction->getOrder();
        $order->setStatus(OrderStatus::ERROR);

        $this->getEntityManager()->flush($order);

        $loggerMock = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('alert')
            ->with($this->stringContains('Unexpected order #2 status (error) when transaction #1 processing'));

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $loggerMock);
        $synchronizer->synchronize($report);
    }

    /**
     * @test
     */
    public function shouldCreateNewTransactionAndUpdateOrderStatusForRefundingReversalTransaction()
    {
        $this->load(true);

        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );

        $reversalOutboundTransactions = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')
            ->findBy(['type' => OutboundTransactionType::REVERSAL]);
        $this->assertCount(0, $reversalOutboundTransactions);

        $order = $outboundTransaction->getOrder();
        $order->setStatus(OrderStatus::SENDING);

        $this->getEntityManager()->flush($order);

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectReversalReportTransaction();
        $transaction->setTransactionId($outboundTransaction->getTransactionId());
        $transaction->setTransactionType(PayDirectReversalReportTransaction::TYPE_REFUNDING);
        $transaction->setAmount($outboundTransaction->getAmount());

        $report->addTransaction($transaction);

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $this->getLoggerMock());
        $synchronizer->synchronize($report);

        $this->getEntityManager()->refresh($outboundTransaction);
        $this->assertEquals(OrderStatus::REFUNDING, $outboundTransaction->getOrder()->getStatus());

        $reversalOutboundTransactions = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')
            ->findBy(['type' => OutboundTransactionType::REVERSAL]);
        $this->assertCount(1, $reversalOutboundTransactions);

        $newOutboundTransaction = $reversalOutboundTransactions[0];
        $this->assertEquals(OutboundTransactionType::REVERSAL, $newOutboundTransaction->getType());
        $this->assertEquals(100, $newOutboundTransaction->getAmount());
        $this->assertEquals($order, $newOutboundTransaction->getOrder());
    }

    /**
     * @test
     */
    public function shouldCreateNewTransactionAndUpdateOrderStatusForReissuedReversalTransaction()
    {
        $this->load(true);

        $outboundTransaction = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')->findOneBy(
            ['transactionId' => 1]
        );

        $reversalOutboundTransactions = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')
            ->findBy(['type' => OutboundTransactionType::REVERSAL]);
        $this->assertCount(0, $reversalOutboundTransactions);

        $order = $outboundTransaction->getOrder();
        $order->setStatus(OrderStatus::SENDING);

        $this->getEntityManager()->flush($order);

        $report = new PaymentProcessorReport();
        $transaction = new PayDirectReversalReportTransaction();
        $transaction->setTransactionId($outboundTransaction->getTransactionId());
        $transaction->setTransactionType(PayDirectReversalReportTransaction::TYPE_REISSUED);
        $transaction->setAmount($outboundTransaction->getAmount());

        $report->addTransaction($transaction);

        $synchronizer = new ReportSynchronizer($this->getEntityManager(), $this->getLoggerMock());
        $synchronizer->synchronize($report);

        $this->getEntityManager()->refresh($outboundTransaction);
        $this->assertEquals(OrderStatus::REISSUED, $outboundTransaction->getOrder()->getStatus());

        $reversalOutboundTransactions = $this->getEntityManager()->getRepository('RjDataBundle:OutboundTransaction')
            ->findBy(['type' => OutboundTransactionType::REVERSAL]);
        $this->assertCount(1, $reversalOutboundTransactions);

        $newOutboundTransaction = $reversalOutboundTransactions[0];
        $this->assertEquals(OutboundTransactionType::REVERSAL, $newOutboundTransaction->getType());
        $this->assertEquals(100, $newOutboundTransaction->getAmount());
        $this->assertEquals($order, $newOutboundTransaction->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }
}
