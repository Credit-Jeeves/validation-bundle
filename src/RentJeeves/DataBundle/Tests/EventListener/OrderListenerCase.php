<?php

namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\CoreBundle\DateTime;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\ExternalApiBundle\Tests\Services\MRI\MRIClientCase;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\BaseTestCase as Base;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;

class OrderListenerCase extends Base
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    /**
     * We test updated startAt on the table rj_contract when user create first order
     *
     * @test
     */
    public function updateStartAtOfContract()
    {
        $this->load(true);
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        /**
         * @var $contract Contract
         */
        $contract = $this->getContract($startAt, $finishAt);
        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->refresh($contract);
        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));
    }

    /**
     * We test do not update startAt on the table rj_contract when user create second order
     *
     * @depends updateStartAtOfContract
     * @test
     */
    public function doNotUpdateStartAtOfContract()
    {
        /**
         * @var $contract Contract
         */
        $contract = $this->getContract();
        $paidFor = new DateTime();
        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor2 = new DateTime();
        $paidFor2->modify('+1 month');
        $operation->setPaidFor($paidFor2);
        $operation->setOrder($order);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->refresh($contract);

        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));
    }

    public function getDataForUpdateBalanceContract()
    {
        return array(
            array(
                $integratedBalanceMustBe = 0.00,
                $balanceOrderMustBe = -500.00,
                $orderAmount = 500.00,
                $orderStatus = OrderStatus::COMPLETE,
                $operationType = OperationType::RENT,
                $isIntegrated = false
            ),
            array(
                $integratedBalanceMustBe = -500.00,
                $balanceOrderMustBe = -500.00,
                $orderAmount = 500.00,
                $orderStatus = OrderStatus::COMPLETE,
                $operationType = OperationType::RENT,
                $isIntegrated = true
            ),
            array(
                $integratedBalanceMustBe = 0.00,
                $balanceOrderMustBe = 500.00,
                $orderAmount = 500.00,
                $orderStatus = OrderStatus::REFUNDED,
                $operationType = OperationType::RENT,
                $isIntegrated = false
            ),
            array(
                $integratedBalanceMustBe = 500.00,
                $balanceOrderMustBe = 500.00,
                $orderAmount = 500.00,
                $orderStatus = OrderStatus::RETURNED,
                $operationType = OperationType::RENT,
                $isIntegrated = true
            )
        );
    }

    /**
     * @dataProvider getDataForUpdateBalanceContract
     * @test
     */
    public function updateBalanceContract(
        $integratedBalanceMustBe,
        $balanceOrderMustBe,
        $orderAmount,
        $orderStatus,
        $operationType,
        $isIntegrated
    ) {
        $this->load(true);
        $today = new DateTime();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contract = new Contract();
        $contract->setRent(999.99);
        $contract->setBalance(999.89);
        $contract->setStartAt(new DateTime("-1 month"));
        $contract->setFinishAt(new DateTime("+5 month"));
        $contract->setPaidTo(new DateTime("+10 days"));
        $contract->setDueDate($today->format('j'));
        $contract->setBalance(0.00);
        $contract->setIntegratedBalance(0.00);

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );

        $this->assertNotNull($tenant);
        $contract->setTenant($tenant);
        if ($isIntegrated) {
            $unitName = '1-a';
        } else {
            $unitName = 'HH-1';
        }
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => $unitName
            )
        );

        $this->assertNotNull($unit);

        $contract->setUnit($unit);
        $contract->setGroup($unit->getGroup());
        $contract->setHolding($unit->getHolding());
        $contract->setProperty($unit->getProperty());
        $contract->setStatus(ContractStatus::CURRENT);
        $em->persist($contract);
        $em->flush();

        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setSum($orderAmount);
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::PENDING);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount($orderAmount);
        $operation->setGroup($contract->getGroup());
        $operation->setType($operationType);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $em->persist($order);
        $em->persist($operation);
        $em->flush();
        $em->refresh($contract);
        $order->setStatus($orderStatus);
        $em->persist($order);
        $em->flush();
        $order->setStatus($orderStatus);
        $em->persist($order);

        $this->assertEquals($balanceOrderMustBe, $contract->getBalance());
        $this->assertEquals($integratedBalanceMustBe, $contract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function shouldUnshiftContractDateWhenOrderIsCancelled()
    {
        $this->load(true);
        $container = static::getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');

        $orders = $em->getRepository('DataBundle:Order')
            ->findBy(
                array(
                    'status' => OrderStatus::COMPLETE,
                    'type' => OrderType::HEARTLAND_CARD,
                    'sum' => 1250.00
                )
            );

        /** @var OrderSubmerchant $order */
        $order = $orders[0];

        /** @var Operation $operation */
        $operation = $order->getOperations()->last();
        $contract = $operation->getContract();
        $currentPaidToDate = clone $contract->getPaidTo();
        $expectedPaidTo = $currentPaidToDate->format('Y-m-d');

        $order->setStatus(OrderStatus::CANCELLED);
        $em->flush();

        $newPaidToDate = $currentPaidToDate->modify('-1 month');
        $actualPaidTo = $newPaidToDate->format('Y-m-d');

        $this->assertNotEquals($expectedPaidTo, $actualPaidTo);
        $this->assertEquals($newPaidToDate, $contract->getPaidTo());
    }

    /**
     * @test
     */
    public function shouldSetCorrectPaidToForOrderWith2RentOperations()
    {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var $tenant Tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'linda@rentrack.com'
            )
        );

        $this->assertNotNull($tenant);
        $this->assertEquals(1, $tenant->getContracts()->count());

        /** @var Contract $contract */
        $contract = $tenant->getContracts()->first();
        $paidTo = $contract->getPaidTo();
        $paidToOriginal = clone $paidTo;

        $order = new OrderSubmerchant();
        $order->setUser($tenant);
        $order->setSum(1000);
        $order->setType(OrderType::HEARTLAND_BANK);
        $order->setStatus(OrderStatus::PENDING);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor1 = clone $paidTo;
        $operation->setPaidFor($paidFor1);
        $operation->setOrder($order);

        $operation2 = new Operation();
        $operation2->setContract($contract);
        $operation2->setAmount(500);
        $operation2->setGroup($contract->getGroup());
        $operation2->setType(OperationType::RENT);
        $paidFor2 = clone $paidTo;
        $paidFor2->modify('+1 months');
        $operation2->setPaidFor($paidFor2);
        $operation2->setOrder($order);

        $em->persist($order);
        $em->persist($operation);
        $em->persist($operation2);
        $em->flush();
        $em->refresh($contract);
        $order->setStatus(OrderStatus::COMPLETE);
        $em->flush($order);

        $this->assertEquals($paidToOriginal->modify('+2 months')->format('mdY'), $contract->getPaidTo()->format('mdY'));
    }

    /**
     * @test
     */
    public function shouldSetEarliestPaidForAsContractStartDate()
    {
        $this->load(true);
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        /**
         * @var $contract Contract
         */
        $contract = $this->getContract($startAt, $finishAt);
        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $operation2 = new Operation();
        $operation2->setContract($contract);
        $operation2->setAmount(500);
        $operation2->setGroup($contract->getGroup());
        $operation2->setType(OperationType::RENT);
        $paidFor2 = clone $paidFor;
        $paidFor2->modify('+1 months');
        $operation2->setPaidFor($paidFor2);
        $operation2->setOrder($order);

        $em->persist($operation);
        $em->persist($operation2);
        $em->persist($order);
        $em->flush();
        $em->refresh($contract);
        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));
    }

    /**
     * @test
     */
    public function shouldSetBatchAndDepositDateForCompleteCCOrdersWhenOnlyOtherAmountExists()
    {
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        /** @var $contract Contract */
        $contract = $this->getContract($startAt, $finishAt);
        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));

        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::NEWONE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setType(OperationType::OTHER);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $transaction = new Transaction();
        $transaction->setAmount(500);
        $transaction->setOrder($order);
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->setIsSuccessful(true);
        $order->addTransaction($transaction);

        $em->persist($operation);
        $em->persist($transaction);
        $em->persist($order);
        $em->flush();

        $this->assertNotNull($transactionId = $transaction->getId());
        $this->assertNull($transaction->getBatchDate());
        $this->assertNull($transaction->getDepositDate());

        // change status to COMPLETE - here is the place where OrderListener:syncTransactions works
        $order->setStatus(OrderStatus::COMPLETE);
        $em->flush($order);

        $this->assertNotNull($newTransaction = $em->find('RjDataBundle:Transaction', $transactionId));
        $this->assertNotNull($batchDate = $newTransaction->getBatchDate());
        $this->assertNotNull($depositDate = $newTransaction->getDepositDate());
        $this->assertEquals((new DateTime())->format('Ymd'), $batchDate->format('Ymd'));
        $this->assertGreaterThanOrEqual(1, $batchDate->diff($depositDate)->format('%r%a'));
    }

    /**
     * @test
     */
    public function shouldMovePaymentPaidForWhenOrderIsComplete()
    {
        $startAt = (new DateTime())->modify('-5 month');
        $finishAt = (new DateTime())->modify('+24 month');
        $contract = $this->getContract($startAt, $finishAt);
        $payment = $this->createPayment($contract);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($payment);
        $em->flush($payment);

        // Run command "payment:pay"
        $application = new Application($this->getKernel());
        $application->add(new PayCommand());
        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertGreaterThanOrEqual(1, $jobs);
        $command = $application->find('payment:pay');
        $commandTester = new CommandTester($command);

        foreach ($jobs as $job) {
            $commandTester->execute(
                array(
                    'command' => $command->getName(),
                    '--jms-job-id' => $job->getId(),
                )
            );
        }
        $this->assertCount(
            1,
            $orders = $em->getRepository('DataBundle:Order')->getContractHistory($contract),
            'One order should be created for the given contract'
        );
        $order = $orders[0];
        $this->assertEquals(OrderStatus::PENDING, $order->getStatus(), 'Verify that order is in PENDING status');
        $expectedPaidFor = clone $payment->getPaidFor();
        $expectedPaidFor->modify('+1 month');
        // here is a moment when payment's paidFor is moved to next month
        $order->setStatus(OrderStatus::COMPLETE);
        $em->flush($order);

        $this->assertNotNull($paymentResult = $em->find('RjDataBundle:Payment', $payment->getId()));
        $actualPaidFor = $paymentResult->getPaidFor();
        $this->assertEquals($expectedPaidFor->format('mdY'), $actualPaidFor->format('mdY'));
    }

    /**
     * @test
     * @depends shouldMovePaymentPaidForWhenOrderIsComplete
     */
    public function shouldCloseRecurringPaymentWhenACHPaymentReturned()
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var $order OrderSubmerchant */
        $this->assertNotNull(
            $order = $em->getRepository('DataBundle:Order')->findOneBySum('999'),
            'Expected order is not found'
        );
        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus());
        $this->assertNotNull(
            $payment = $order->getContract()->getActivePayment(),
            'Active payment for contract not found'
        );
        $order->setStatus(OrderStatus::RETURNED);
        $em->flush($order);
        // Reload payment from the DB
        $resultPayment = $em->find('RjDataBundle:Payment', $payment->getId());

        $this->assertEquals(PaymentStatus::CLOSE, $resultPayment->getStatus());
        $this->assertCount(2, $resultPayment->getCloseDetails(), 'Payment close details should be an array of 2 items');
        $this->assertContains(PaymentCloseReason::RECURRING_RETURNED, $resultPayment->getCloseDetails()['1']);
    }

    protected function createPayment(Contract $contract)
    {
        $tenant = $contract->getTenant();
        $paymentAccount = $tenant->getPaymentAccounts()->filter(
            function ($paymentAccount) {
                if (PaymentAccountType::BANK == $paymentAccount->getType()) {
                    return true;
                }

                return false;
            }
        )->first();

        $payment = new Payment();
        $payment->setAmount(999);
        $payment->setTotal(999);
        $payment->setType(PaymentType::RECURRING);
        $payment->setStatus(PaymentStatus::ACTIVE);
        $payment->setContract($contract);
        $payment->setPaymentAccount($paymentAccount);
        $today = new DateTime();
        $payment->setDueDate($today->format('j'));
        $payment->setStartMonth($today->format('n'));
        $payment->setStartYear($today->format('Y'));
        $paidFor = (new DateTime())->setDate(2015, 1, 1);
        $payment->setPaidFor($paidFor);

        return $payment;
    }

    public function dataForCreatePaymentPushCommand()
    {
        return [
            [
                ApiIntegrationType::RESMAN,
                ResManClientCase::RESIDENT_ID,
                ResManClientCase::EXTERNAL_PROPERTY_ID,
                ResManClientCase::EXTERNAL_LEASE_ID
            ],
            [
                ApiIntegrationType::MRI,
                MRIClientCase::RESIDENT_ID,
                MRIClientCase::PROPERTY_ID,
                null
            ]
        ];
    }

    /**
     * @param $apiIntegrationType
     * @param $residentId
     * @param $externalPropertyId
     * @param $externalLeaseId
     *
     * @test
     * @dataProvider dataForCreatePaymentPushCommand
     */
    public function shouldCreatePaymentPushCommand(
        $apiIntegrationType,
        $residentId,
        $externalPropertyId,
        $externalLeaseId
    ) {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:transaction:push']
        );

        $this->createTransaction(
            $apiIntegrationType,
            $residentId,
            $externalPropertyId,
            $externalLeaseId
        );

        $this->assertCount(0, $jobs);

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $this->assertCount(1, $jobs);
    }
}
