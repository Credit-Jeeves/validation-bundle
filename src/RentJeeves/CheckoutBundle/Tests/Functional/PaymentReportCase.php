<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Heartland as HeartlandTransaction;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\DataBundle\Entity\Tenant;

class PaymentReportCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provideReversal
     */
    public function shouldSynchronizeDBOrdersWithReversalReport($transactionId, $firstStatus, $secondStatus)
    {
        $this->load(true);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $paymentReport = $this->getContainer()->get('payment.reversal_report');

        $repo = $em->getRepository('RjDataBundle:Heartland');
        $transaction = $repo->findOneBy(array('transactionId' => $transactionId));
        $order = $transaction->getOrder();

        $this->assertEquals($firstStatus, $order->getStatus());

        $paymentReport->synchronize();

        $this->assertEquals($secondStatus, $order->getStatus());
    }

    public function provideReversal()
    {
        return array(
            array('369369', 'complete', 'returned'),
            array('123123', 'complete', 'refunded'),
            array('456456', 'complete', 'cancelled'),
        );
    }

    /**
     * @test
     */
    public function shouldSynchronizeDBOrdersWithDepositReport()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $transactionId = 5355372;
        $this->createOrder($transactionId);

        $paymentReport = $this->getContainer()->get('payment.deposit_report');
        $paymentReport->synchronize();

        $repo = $em->getRepository('RjDataBundle:Heartland');
        /** @var HeartlandTransaction $resultTransaction */
        $this->assertNotNull($resultTransaction = $repo->findOneBy(array('transactionId' => $transactionId)));
        $this->assertNotNull($batchDate = $resultTransaction->getBatchDate());
        $this->assertNotNull($depositDate = $resultTransaction->getDepositDate());
        $this->assertEquals('8/1/2014', $batchDate->format('n/j/Y'));
        $this->assertEquals('8/5/2014', $depositDate->format('n/j/Y'));
        $this->assertNotNull($resultOrder = $resultTransaction->getOrder());
        $this->assertEquals(OrderStatus::COMPLETE, $resultOrder->getStatus());
        $this->assertEquals('MrchntNm', $resultTransaction->getMerchantName());
    }

    /**
     * @test
     */
    public function shouldSynchronizeDBOrdersWithDepositReportAndNotSetDepositDate()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $transactionId = 5355373;
        $this->createOrder($transactionId);

        $paymentReport = $this->getContainer()->get('payment.deposit_report');
        $paymentReport->synchronize();

        $repo = $em->getRepository('RjDataBundle:Heartland');
        /** @var HeartlandTransaction $resultTransaction */
        $this->assertNotNull($resultTransaction = $repo->findOneBy(array('transactionId' => $transactionId)));
        $this->assertNotNull($batchDate = $resultTransaction->getBatchDate());
        $this->assertEquals(null, $resultTransaction->getDepositDate());
        $this->assertNotNull($resultOrder = $resultTransaction->getOrder());
        $this->assertNotEquals(OrderStatus::COMPLETE, $resultOrder->getStatus());
    }

    /**
     * @test
     */
    public function shouldFillEmptyBatchIdForCompleteTransactions()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Heartland');

        $transactionId = 789789;
        /** @var HeartlandTransaction $transaction */
        $transaction = $repo->findOneBy(array('transactionId' => $transactionId));
        $this->assertNotNull($transaction);

        // It would be better to add a new transaction fixture to the database,
        // but then we'd have to fix several related tests that check the exact amount of transactions,
        // that's why we use one of the existent transactions.
        $this->assertEquals(111555, $transaction->getBatchId(), 'Verify expected test fixture exists');
        $transaction->setBatchId(null);
        $em->flush($transaction);

        $paymentReport = $this->getContainer()->get('payment.reversal_report');
        $paymentReport->synchronize();

        /** @var HeartlandTransaction $resultTransaction */
        $this->assertNotNull($resultTransaction = $repo->findOneBy(array('transactionId' => $transactionId)));
        // 145176 is a value from heartland report file fixture
        $this->assertEquals(145176, $resultTransaction->getBatchId(), 'Batch id was not updated');
    }

    protected function createOrder($transactionId)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING);
        $order->setType(OrderType::HEARTLAND_BANK);
        $order->setSum(999);
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => 'tenant11@example.com'));
        $order->setUser($tenant);

        $operation = new Operation();
        $operation->setAmount(999);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setPaidFor(new DateTime('8/1/2014'));
        $operation->setContract($tenant->getContracts()->last());

        $transaction = new HeartlandTransaction();
        $transaction->setIsSuccessful(true);
        $transaction->setOrder($order);
        $transaction->setTransactionId($transactionId);
        $transaction->setAmount(999);
        $transaction->setMerchantName('MrchntNm');

        $em->persist($order);
        $em->persist($operation);
        $em->persist($transaction);
        $em->flush();
    }
}
