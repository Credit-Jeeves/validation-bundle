<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\ReportLoader;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PaymentReportCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\DataBundle\Entity\Transaction as HeartlandTransaction;

class PaymentReportCase extends BaseTestCase
{
    public function setUp()
    {
        $this->load(true);

        $this->hpsReportPath = __DIR__ . '/../Fixtures/hps/';
        $this->depositFile = $this->hpsReportPath . 'report_' . ReportLoader::DEPOSIT_REPORT_FILENAME_SUFFIX . '.csv';
        $this->reversalFile = $this->hpsReportPath . 'report_' . ReportLoader::REVERSAL_REPORT_FILENAME_SUFFIX . '.csv';
        $this->hpsACHDepositReport = file_get_contents($this->depositFile);
        $this->hpsBillDataReport = file_get_contents($this->reversalFile);
    }

    public function tearDown()
    {
        // remove archive dir
        $filesystem = new Filesystem();
        $filesystem->remove($this->hpsReportPath . 'archive');

        // create report fixtures
        $filesystem->dumpFile($this->depositFile, $this->hpsACHDepositReport);
        $filesystem->dumpFile($this->reversalFile, $this->hpsBillDataReport);
    }

    protected function executeCommand()
    {
        $application = new Application($this->getKernel());
        $application->add(new PaymentReportCommand());

        $command = $application->find('payment:report:synchronize');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName()
            ]
        );

        return $commandTester->getDisplay();
    }

    /**
     * @test
     */
    public function shouldExecuteCommandAndSendEmails()
    {
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $result = $this->executeCommand();

        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(9, $count); // +2 for Monolog Message
        $this->assertContains('Amount of synchronized payments: 11', $result);
    }

    /**
     * @test
     */
    public function shouldNotSendEmailsTwice()
    {
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommand();

        $this->assertNotNull($count = $plugin->getPreSendMessages());
        $this->assertCount(9, $count); // +2 for Monolog Message

        // get all report files back to dir
        $this->tearDown();

        $plugin->clean();
        $this->executeCommand();

        $this->assertCount(2, $plugin->getPreSendMessages()); // 2 for Monolog Message
    }

    /**
     * @test
     */
    public function shouldCreateReversalTransactionForVoidedCCPayment()
    {
        $this->executeCommand();

        $originalTransId = 258258;
        $voidTransId = 258259;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var HeartlandTransaction $originalTransaction */
        $originalTransaction = $em->getRepository('RjDataBundle:Transaction')->findOneByTransactionId($originalTransId);
        $this->assertNotNull($originalTransaction);
        /** @var HeartlandTransaction $voidTransaction */
        $voidTransaction = $em->getRepository('RjDataBundle:Transaction')->findOneByTransactionId($voidTransId);
        $this->assertNotNull($voidTransaction);
        $this->assertNull($originalTransaction->getDepositDate());
        $this->assertEquals(0, $originalTransaction->getAmount() + $voidTransaction->getAmount());
        $this->assertSame($originalTransaction->getOrder(), $voidTransaction->getOrder());
        $this->assertEquals(OrderStatus::CANCELLED, $originalTransaction->getOrder()->getStatus());
        $this->assertEquals(TransactionStatus::REVERSED, $voidTransaction->getStatus());
    }

    /**
     * @return array
     */
    public function provideReversal()
    {
        return [
            ['369369', OrderStatus::COMPLETE, OrderStatus::RETURNED],
            ['778899', OrderStatus::COMPLETE, OrderStatus::REFUNDED],
            ['123123', OrderStatus::COMPLETE, OrderStatus::REFUNDING],
            ['456456', OrderStatus::COMPLETE, OrderStatus::CANCELLED],
        ];
    }

    /**
     * @param int $transactionId
     * @param string $firstStatus one of OrderStatus
     * @param string $secondStatus one of OrderStatus
     *
     * @test
     * @dataProvider provideReversal
     */
    public function shouldSynchronizeDBOrdersWithReversalReport($transactionId, $firstStatus, $secondStatus)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Transaction $transaction */
        $transaction = $em->getRepository('RjDataBundle:Transaction')->findOneBy(['transactionId' => $transactionId]);
        $order = $transaction->getOrder();

        $this->assertEquals($firstStatus, $order->getStatus());

        $this->executeCommand();
        /** @var  Order $updatedOrder */
        $this->assertNotNull($updatedOrder = $em->getRepository('DataBundle:Order')->find($order->getId()));
        $this->assertEquals($secondStatus, $updatedOrder->getStatus());
    }

    /**
     * @test
     */
    public function shouldSynchronizeDBOrdersWithDepositReport()
    {
        $transactionId = 5355372;
        $this->createOrder($transactionId);

        $this->executeCommand();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Transaction');
        /** @var HeartlandTransaction $resultTransaction */
        $this->assertNotNull($resultTransaction = $repo->findOneBy(['transactionId' => $transactionId]));
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
        $transactionId = 5355373;
        $this->createOrder($transactionId);

        $this->executeCommand();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Transaction');
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
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('RjDataBundle:Transaction');

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

        $this->executeCommand();

        /** @var HeartlandTransaction $resultTransaction */
        $this->assertNotNull($resultTransaction = $repo->findOneBy(array('transactionId' => $transactionId)));
        // 145176 is a value from heartland report file fixture
        $this->assertEquals(145176, $resultTransaction->getBatchId(), 'Batch id was not updated');
    }

    /**
     * @test
     */
    public function shouldNotMoveAlreadyReversedOrderToComplete()
    {
        $em = $this->getEntityManager();
        /** @var Order $order */
        $order = $em->find('DataBundle:Order', 8); // RETURNED order with deposit and reversed transactions
        $this->assertNotNull($order, 'Order #8 not found');
        $this->assertEquals(OrderStatus::RETURNED, $order->getStatus());
        $this->assertCount(2, $order->getTransactions(), 'Order should have 2 transactions');
        $this->assertInstanceOf(
            'RentJeeves\DataBundle\Entity\Transaction',
            $reversedTransaction = $order->getReversedTransaction()
        );
        $this->assertInstanceOf(
            'RentJeeves\DataBundle\Entity\Transaction',
            $depositTransaction = $order->getCompleteTransaction()
        );

        // set depositDate to NULL, then process report and make sure depositDate is set, but orderStatus is not changed
        $depositTransaction->setDepositDate(null);
        $em->flush($depositTransaction);

        $this->executeCommand();

        $em->refresh($depositTransaction);
        $em->refresh($order);
        $this->assertNotNull($depositTransaction->getDepositDate(), 'Deposit transaction should have deposit date');
        $this->assertEquals(
            '2015-08-05',
            $depositTransaction->getDepositDate()->format('Y-m-d'),
            'Deposit date should be +1 business day from date in the report'
        );
        $this->assertEquals(OrderStatus::RETURNED, $order->getStatus(), 'Order status should remain RETURNED');
    }

    protected function createOrder($transactionId)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $order = new OrderSubmerchant();
        $order->setStatus(OrderStatus::PENDING);
        $order->setPaymentType(OrderPaymentType::BANK);
        $order->setSum(999);
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(array('email' => 'tenant11@example.com'));
        $order->setUser($tenant);

        /** @var Contract $contract */
        $contract = $tenant->getContracts()->last();
        $order->setDepositAccount($contract->getGroup()->getRentDepositAccountForCurrentPaymentProcessor());

        $operation = new Operation();
        $operation->setAmount(999);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setPaidFor(new DateTime('8/1/2014'));
        $operation->setContract($contract);

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
