<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\AdjustmentParser;
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
            ->with('Items found in the node "REISSUED_STOPPED_CHECKS"');

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
