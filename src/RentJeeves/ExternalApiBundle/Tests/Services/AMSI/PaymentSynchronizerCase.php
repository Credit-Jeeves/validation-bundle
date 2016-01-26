<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;
use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILedgerClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class PaymentSynchronizerCase extends BaseTestCase
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    /**
     * @test
     */
    public function shouldSendPaymentToAmsiAndReturnTrue()
    {
        $this->markTestSkipped('AMSI sandbox expired. Skipping all AMSI functional tests.');
        $this->load(true);

        $transaction = $this->createTransaction(
            AccountingSystem::AMSI,
            '4492',
            '001',
            13,
            '001|01|101'
        );

        $order = $transaction->getOrder();

        $synchronizer = $this->getSynchronizer();
        $response = $synchronizer->sendOrderToAccountingSystem($order);
        $this->assertTrue($response);

        return $order;
    }

    /**
     * @test
     * @depends shouldSendPaymentToAmsiAndReturnTrue
     */
    public function shouldReturnPaymentForOrderAndReturnTrue(Order $order)
    {
        $this->markTestSkipped('AMSI sandbox expired. Skipping all AMSI functional tests.');
        $order->setStatus(OrderStatus::CANCELLED);

        $completedTransaction = $order->getCompleteTransaction();

        $transaction = new Transaction();
        $transaction->setTransactionId(rand(9999, 9999999));
        $transaction->setAmount($completedTransaction->getAmount());
        $transaction->setIsSuccessful(true);
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setMessages('Test message');
        $transaction->setBatchId(rand(9999, 9999999));
        $transaction->setOrder($order);

        $order->addTransaction($transaction);

        $this->getEntityManager()->flush();

        $settings = $order->getContract()->getHolding()->getAmsiSettings();

        $amsiLedgerClient = $this->createAmsiLedgerClient($settings);
        $response = $amsiLedgerClient->returnPayment($order);

        $this->assertTrue($response);

        return $order;
    }

    /**
     * @test
     * @depends shouldSendPaymentToAmsiAndReturnTrue
     */
    public function shouldUpdateSettlementDataAndReturnTrue(Order $order)
    {
        $this->markTestSkipped('AMSI sandbox expired. Skipping all AMSI functional tests.');
        $settings = $order->getContract()->getHolding()->getAmsiSettings();

        $settlementDate = $this->getSettlementData()->getSettlementDate(
            $order->getReversedTransaction()->getBatchDate(),
            $order->getReversedTransaction()->getDepositDate()
        );

        $amsiLedgerClient = $this->createAmsiLedgerClient($settings);
        $response = $amsiLedgerClient->updateSettlementData(
            $order->getCompleteTransaction()->getTransactionId(),
            $order->getContract()->getGroupId(),
            $order->getSum(),
            $settlementDate
        );

        $this->assertTrue($response);
    }

    /**
     * @return \RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer
     */
    protected function getSynchronizer()
    {
        return $this->getContainer()->get('accounting.payment_sync');
    }

    /**
     * @param SettingsInterface $settings
     *
     * @return AMSILedgerClient
     */
    protected function createAmsiLedgerClient(SettingsInterface $settings)
    {
        $clientFactory = $this->getContainer()->get('soap.client.factory');

        return $clientFactory->getClient($settings, SoapClientEnum::AMSI_LEDGER);
    }

    protected function getSettlementData()
    {
        return $this->getContainer()->get('accounting.amsi_settlement');
    }
}
