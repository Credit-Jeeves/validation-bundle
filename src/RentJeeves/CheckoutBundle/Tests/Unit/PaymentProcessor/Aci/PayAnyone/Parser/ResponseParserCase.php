<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\PayAnyone\Parser;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser\ResponseParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class ResponseParserCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateObjectAndInstanceOfRightClass()
    {
        $parser = new ResponseParser($this->getSerializer(), $this->getLoggerMock());
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
        $pathToFile = $this->getFileLocator()->locate('@RjCheckoutBundle/Tests/Fixtures/Aci/testResponseFile.xml');
        $xml = file_get_contents($pathToFile);

        $logger = $this->getLoggerMock();

        $parser = new ResponseParser($this->getSerializer(), $logger);

        $transactions = $parser->parse($xml);

        $this->assertCount(7, $transactions);
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[0]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[1]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[2]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[3]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[4]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[5]
        );
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction',
            $transactions[6]
        );
        /** @var PayDirectResponseReportTransaction $firstTransaction */
        $firstTransaction = $transactions[0];
        $this->assertEquals('165641', $firstTransaction->getBatchId());
        $this->assertEquals(320, $firstTransaction->getAmount());
        $this->assertEquals('102497630', $firstTransaction->getTransactionId());
        $this->assertEquals('READY TO DISBURSE', $firstTransaction->getResponseCode());
        $this->assertEquals('UNKNOWN_BILLER', $firstTransaction->getResponseMessage());
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
