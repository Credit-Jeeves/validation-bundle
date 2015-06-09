<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\ResponseParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PayDirectResponseReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class ResponseParserCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateObjectAndInstanceOfRightClass()
    {
        $parser = new ResponseParser($this->getSerializer(), $this->getLogger());
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
        $pathToFile = $this->getFileLocator()->locate('@RjCheckoutBundle/Tests/Fixtures/Aci/testResponseFile.xml');
        $xml = file_get_contents($pathToFile);

        $parser = new ResponseParser($this->getSerializer(), $this->getLogger());

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
        $this->assertEquals('WAITING FOR FUNDS', $firstTransaction->getResponseCode());
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
     * @return \Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->getContainer()->get('logger');
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
