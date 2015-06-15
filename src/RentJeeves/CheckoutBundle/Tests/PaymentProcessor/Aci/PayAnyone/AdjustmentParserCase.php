<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\AdjustmentParser;
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
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\AbstractParser',
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
        $logger->expects($this->once())
            ->method('alert')
            ->with('Unsupported transaction found in Aci PayAnyone report node: REISSUED_STOPPED_CHECKS.');

        $parser = new AdjustmentParser($this->getSerializer(), $logger);

        $transactions = $parser->parse($xml);

        $this->assertCount(5, $transactions);
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
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectReversalReportTransaction',
            $transactions[4]
        );
        /** @var PayDirectDepositReportTransaction $firstDepositTransaction */
        $firstDepositTransaction = $transactions[0];
        $this->assertEquals(null, $firstDepositTransaction->getBatchId());
        $this->assertEquals('2015-06-03', $firstDepositTransaction->getDepositDate()->format('Y-m-d'));
        $this->assertEquals('97055778', $firstDepositTransaction->getTransactionId());
        $this->assertEquals(999, $firstDepositTransaction->getAmount());
        /** @var PayDirectReversalReportTransaction $firstReversalTransaction */
        $firstReversalTransaction = $transactions[2];
        $this->assertEquals(null, $firstReversalTransaction->getBatchId());
        $this->assertEquals('2015-06-03', $firstReversalTransaction->getTransactionDate()->format('Y-m-d'));
        $this->assertEquals('97055769', $firstReversalTransaction->getTransactionId());
        $this->assertEquals(999, $firstReversalTransaction->getAmount());
        $this->assertEquals('', $firstReversalTransaction->getReversalDescription());
        $this->assertEquals('refund', $firstReversalTransaction->getTransactionType());
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
