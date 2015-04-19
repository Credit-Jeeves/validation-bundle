<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\AciCollectPay\Report;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\AciCollectPay\Report\LockboxParser;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction;
use RentJeeves\TestBundle\BaseTestCase;

class LockboxParserCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldParseLockboxDataIntoPaymentProcessorReport()
    {
        $data = file_get_contents(__DIR__ . '/../../../../Fixtures/Aci/lockbox.csv');

        $parser = new LockboxParser($this->getContainer()->get('logger'));
        $report = $parser->parse($data);

        $this->assertInstanceOf('RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReport', $report);
        $this->assertCount(2, $report->getTransactions());
        $this->assertInstanceOf(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction',
            $report->getTransactions()[0]
        );
        $this->assertInstanceOf(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Report\DepositReportTransaction',
            $report->getTransactions()[1]
        );

        /** @var DepositReportTransaction $transaction */
        $transaction = $report->getTransactions()[0];
        $this->assertEquals('6979285', $transaction->getTransactionId());
        $this->assertEquals('10000.00', $transaction->getAmount());
        $this->assertEquals('03242015', $transaction->getDepositDate()->format('mdY'));
    }
}
