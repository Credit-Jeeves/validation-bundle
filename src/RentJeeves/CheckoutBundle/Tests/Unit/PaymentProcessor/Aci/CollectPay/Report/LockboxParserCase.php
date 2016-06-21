<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay\Report;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report\LockboxParser;
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
        $data = file_get_contents(__DIR__ . '/../../../../../Data/PaymentProcessor/Aci/CollectPay/Report/lockbox.csv');

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
    public function shouldParseLockboxDataIntoArrayWithDebitTransactionsAndSkipZeroAmountTransaction()
    {
        $data = file_get_contents(
            __DIR__ . '/../../../../../Data/PaymentProcessor/Aci/CollectPay/Report/debitsLockbox.csv'
        );

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

        /** @var ReversalReportTransaction $returnedTransaction */
        $returnedTransaction = $decodedData[0];
        $this->assertEquals('7019551', $returnedTransaction->getTransactionId());
        $this->assertEquals('3.11', $returnedTransaction->getAmount());
        $this->assertEquals('05292015', $returnedTransaction->getTransactionDate()->format('mdY'));
        $this->assertEquals('19 : Account Closed', $returnedTransaction->getReversalDescription());
        $this->assertEquals($returnedTransaction->getTransactionId(), $returnedTransaction->getOriginalTransactionId());
        $this->assertEquals(ReversalReportTransaction::TYPE_RETURN, $returnedTransaction->getTransactionType());

        /** @var ReversalReportTransaction $refundedTransaction */
        $refundedTransaction = $decodedData[1];
        $this->assertEquals('7020506', $refundedTransaction->getTransactionId());
        $this->assertEquals('101.00', $refundedTransaction->getAmount());
        $this->assertEquals('05292015', $refundedTransaction->getTransactionDate()->format('mdY'));
        $this->assertNotEquals(
            $refundedTransaction->getTransactionId(),
            $refundedTransaction->getOriginalTransactionId()
        );
        $this->assertEquals(ReversalReportTransaction::TYPE_REFUND, $refundedTransaction->getTransactionType());
    }
}
