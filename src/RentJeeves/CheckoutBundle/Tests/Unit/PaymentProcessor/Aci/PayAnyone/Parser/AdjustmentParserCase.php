<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\PayAnyone\Parser;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\AdjustmentParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectDepositReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class AdjustmentParserCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateObjectAndInstanceOfRightClass()
    {
        $parser = new AdjustmentParser($this->getSerializer(), $this->getLoggerMock());
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\AbstractParser',
            $parser
        );
    }

    /**
     * @test
     */
    public function shouldReturnTransactionsAfterParse()
    {
        $pathToFile = $this->getFileLocator()->locate('@RjCheckoutBundle/Tests/Fixtures/Aci/testAdjustFile.xml');
        $xml = file_get_contents($pathToFile);

        $logger = $this->getLoggerMock();
        //REFUNDED_RETURNED_PAYMENTS, CORRECTED_DUPLICATE_PAYMENTS, STOPPED_CHECKS
        $logger->expects($this->any(3))
            ->method('alert');

        $parser = new AdjustmentParser($this->getSerializer(), $logger);

        $transactions = $parser->parse($xml);

        $this->assertCount(4, $transactions);
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectDepositReportTransaction',
            $transactions[0]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectDepositReportTransaction',
            $transactions[1]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction',
            $transactions[2]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction',
            $transactions[3]
        );
        /** @var PayDirectDepositReportTransaction $firstDepositTransaction */
        $firstDepositTransaction = $transactions[0];
        $this->assertEquals(null, $firstDepositTransaction->getBatchId());
        $this->assertEquals('2015-06-03', $firstDepositTransaction->getDepositDate()->format('Y-m-d'));
        $this->assertEquals('97055778', $firstDepositTransaction->getTransactionId());
        $this->assertEquals(999, $firstDepositTransaction->getAmount());
        /** @var PayDirectReversalReportTransaction $refundingReversalTransaction */
        $refundingReversalTransaction = $transactions[2];
        $this->assertEquals(null, $refundingReversalTransaction->getBatchId());
        $this->assertEquals('2015-06-03', $refundingReversalTransaction->getTransactionDate()->format('Y-m-d'));
        $this->assertEquals('97055769', $refundingReversalTransaction->getTransactionId());
        $this->assertEquals(999, $refundingReversalTransaction->getAmount());
        $this->assertEquals('', $refundingReversalTransaction->getReversalDescription());
        $this->assertEquals('refunding', $refundingReversalTransaction->getTransactionType());
        /** @var PayDirectReversalReportTransaction $reissuedReversalTransaction */
        $reissuedReversalTransaction = $transactions[3];
        $this->assertEquals(null, $reissuedReversalTransaction->getBatchId());
        $this->assertEquals('2015-06-03', $reissuedReversalTransaction->getTransactionDate()->format('Y-m-d'));
        $this->assertEquals('97055775', $reissuedReversalTransaction->getTransactionId());
        $this->assertEquals(999, $reissuedReversalTransaction->getAmount());
        $this->assertEquals('', $reissuedReversalTransaction->getReversalDescription());
        $this->assertEquals('reissued', $reissuedReversalTransaction->getTransactionType());
    }

    /**
     * @return \JMS\Serializer\Serializer
     */
    protected function getSerializer()
    {
        return $this->getContainer()->get('serializer');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', [], [], '', false);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
