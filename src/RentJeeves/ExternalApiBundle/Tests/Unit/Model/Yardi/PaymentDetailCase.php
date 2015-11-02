<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Model\Yardi;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\DataBundle\Enum\YardiNsfPostMonthOption;
use RentJeeves\ExternalApiBundle\Model\Yardi\PaymentDetail;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class PaymentDetailCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldUseOrderCreatedAtAsReturnTransactionDateIfNsfPostMonthIsOriginalTransactionDate()
    {
        $order = $this->getOrder(YardiNsfPostMonthOption::ORIGINAL_TRANSACTION_DATE);
        $order->setCreatedAt(new \DateTime('2015-01-01'));

        $paymentDetail = new PaymentDetail($order);

        $this->assertEquals('2015-01-01', $paymentDetail->getReturnTransactionDate());
    }

    /**
     * @test
     */
    public function shouldUseReversedDepositDateAsReturnTransactionDateIfNsfPostMonthIsReturnTransactionDate()
    {
        $order = $this->getOrder(YardiNsfPostMonthOption::RETURN_TRANSACTION_DATE);
        $order->setCreatedAt(new \DateTime('2015-12-31'));

        $transaction = new Transaction();
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setIsSuccessful(true);
        $transaction->setCreatedAt(new \DateTime('2017-01-01'));
        $transaction->setDepositDate(new \DateTime('2017-01-31'));
        $transaction->setOrder($order);
        $order->addTransaction($transaction);

        $paymentDetail = new PaymentDetail($order);

        $this->assertEquals('2017-01-31', $paymentDetail->getReturnTransactionDate());

    }

    /**
     * @test
     */
    public function shouldUseOrderCreatedAtAsReturnTransactionDateIfNsfPostMonthIsReturnTransactionDteAndNoDepositDate()
    {
        $order = $this->getOrder(YardiNsfPostMonthOption::RETURN_TRANSACTION_DATE);
        $order->setCreatedAt(new \DateTime('2015-12-31'));

        $transaction = new Transaction();
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setIsSuccessful(true);
        $transaction->setCreatedAt(new \DateTime('2017-01-01'));
        $transaction->setOrder($order);
        $order->addTransaction($transaction);

        $paymentDetail = new PaymentDetail($order);

        $this->assertEquals('2015-12-31', $paymentDetail->getReturnTransactionDate());

    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Order does not have reversal transaction
     */
    public function shouldThrowExceptionIfNsfPostMonthIsReturnTransactionDateAndNoReversedTransaction()
    {
        $order = $this->getOrder(YardiNsfPostMonthOption::RETURN_TRANSACTION_DATE);
        $paymentDetail = new PaymentDetail($order);

        $paymentDetail->getReturnTransactionDate();
    }

    /**
     * @param string $nsfPostMonthOption
     * @return OrderSubmerchant
     */
    protected function getOrder($nsfPostMonthOption)
    {
        $order = new OrderSubmerchant();

        $yardiSettings = new YardiSettings();
        $yardiSettings->setNsfPostMonthNode($nsfPostMonthOption);
        $holding = new Holding();
        $holding->setYardiSettings($yardiSettings);
        $contract = new Contract();
        $contract->setHolding($holding);
        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setOrder($order);
        $order->addOperation($operation);

        return $order;
    }
}
