<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\AciCollectPay\Report;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay\Report\LockboxParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class LockboxParserCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldParseLockboxDataIntoArrayWithTransactions()
    {
        $data = file_get_contents(__DIR__ . '/../../../../../Fixtures/Aci/lockbox.csv');

        $parser = new LockboxParser($this->getContainer()->get('logger'));
        $decodedData = $parser->parse($data);

        $this->assertTrue(is_array($decodedData));
        $this->assertCount(2, $decodedData);
        $this->assertInstanceOf(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction',
            $decodedData[0]
        );
        $this->assertInstanceOf(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction',
            $decodedData[1]
        );

        /** @var DepositReportTransaction $transaction */
        $transaction = $decodedData[0];
        $this->assertEquals('6979285', $transaction->getTransactionId());
        $this->assertEquals('10000.00', $transaction->getAmount());
        $this->assertEquals('03242015', $transaction->getDepositDate()->format('mdY'));
    }

    /**
     * @test
     */
    public function shouldParseLockboxDataIntoArrayWithDebitTransactions()
    {
        $data = file_get_contents(__DIR__ . '/../../../../../Fixtures/Aci/debitsLockbox.csv');

        $parser = new LockboxParser($this->getContainer()->get('logger'));
        $decodedData = $parser->parse($data);

        $this->assertTrue(is_array($decodedData));
        $this->assertCount(2, $decodedData);
        $this->assertInstanceOf(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction',
            $decodedData[0]
        );
        $this->assertInstanceOf(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\ReversalReportTransaction',
            $decodedData[1]
        );

        /** @var ReversalReportTransaction $transaction */
        $transaction = $decodedData[0];
        $this->assertEquals('7019551', $transaction->getTransactionId());
        $this->assertEquals('1.11', $transaction->getAmount());
        $this->assertEquals('5292015', $transaction->getTransactionDate()->format('mdY'));
        $this->assertEquals('19 : Account Closed', $transaction->getReversalDescription());
    }
}
