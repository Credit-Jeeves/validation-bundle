<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use ACI\Utils\OldProfilesStorage;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use Payum\AciCollectPay\Model\Profile;
use Payum\AciCollectPay\Request\ProfileRequest\DeleteProfile;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay;
use RentJeeves\CheckoutBundle\Services\PaymentAccountTypeMapper\PaymentAccount as PaymentAccountData;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\BankAccountType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use Symfony\Component\Config\FileLocator;
use Ton\EmailBundle\EventListener\EmailListener;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentCommandsCase extends BaseTestCase
{
    use OldProfilesStorage;
    /**
     * @var EmailListener
     */
    protected $plugin;
    /**
     * @var FileLocator
     */
    protected $fixtureLocator;

    protected function setUp()
    {
        $this->load(true);
        $this->plugin = $this->registerEmailListener();
        $this->plugin->clean();

        $this->fixtureLocator = new FileLocator(
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures']
        );
    }

    protected function executePayCommand($jobId)
    {
        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $command = $application->find('payment:pay');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--jms-job-id' => $jobId,
            )
        );

        return $commandTester;
    }

    /**
     * @return OrderStatusManagerInterface
     */
    protected function getOrderStatusManager()
    {
        return $this->getContainer()->get('payment_processor.order_status_manager');
    }

    /**
     * @return array
     */
    public function providerForCheckingOptionIsPaymentsEnabled()
    {
        return [
            [true, 1],
            [false, 0]
        ];
    }

    /**
     * @test
     * @dataProvider providerForCheckingOptionIsPaymentsEnabled
     *
     * @param boolean $isPaymentsEnabled
     * @param integer $jobsCount
     */
    public function shouldCheckOptionIsPaymentsEnabled($isPaymentsEnabled, $jobsCount)
    {
        $this->load(true);
        /** @var Holding $holding */
        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $holding->setPaymentsEnabled($isPaymentsEnabled);
        $this->getEntityManager()->flush();

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount($jobsCount, $jobs);
    }

    /**
     * @test
     */
    public function collectAndPay()
    {
        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(1, $jobs);

        $commandTester = $this->executePayCommand($jobs[0]->getId());

        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());
        $this->assertCount(1, $this->plugin->getPreSendMessages());
        $this->assertEquals('Rent Payment Receipt', $this->plugin->getPreSendMessage(0)->getSubject());

        $this->plugin->clean();

        $commandTester = $this->executePayCommand($jobs[0]->getId());
        $this->assertRegExp("/Start\nPayment already executed./", $commandTester->getDisplay());
        $this->assertCount(0, $this->plugin->getPreSendMessages());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(0, $jobs);
    }

    /**
     * @test
     */
    public function collectCreditTrackAndPay()
    {
        $jobs = $this->getContainer()->get('doctrine')
            ->getRepository('RjDataBundle:PaymentAccount')
            ->collectCreditTrackToJobs();
        // if today is 31, just skip this test (fixtures can't work correctly for 31st)
        $today = new DateTime();
        if (31 == $today->format('j')) {
            $this->assertCount(0, $jobs);
        } else {
            $commandTester = $this->executePayCommand($jobs[0]->getId());

            $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());

            $this->assertCount(1, $this->plugin->getPreSendMessages());
            $this->assertEquals('Receipt from Rent Track', $this->plugin->getPreSendMessage(0)->getSubject());
        }
    }

    /**
     * @test
     */
    public function collectAndPayBalanceOnlyWhenBalanceGreaterThanRent()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var Contract $contract */
        $contract = $this->getContract($em);
        $rentAmount = $contract->getRent();

        $amount = $rentAmount * 2 + 25;
        $contract->setIntegratedBalance($amount);
        $groupSettings = $contract->getGroup()->getGroupSettings();
        $groupSettings->setPayBalanceOnly(true);
        $groupSettings->setIsIntegrated(true);

        $payment = $this->createPayment($contract, $amount);
        $em->persist($payment);
        $em->flush($payment);

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommand();

        // "Your Rent is Processing" Email
        $this->assertCount(2, $plugin->getPreSendMessages());

        /** @var OrderSubmerchant $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => $amount));
        $this->assertNotNull($order);
        $this->assertNotNull($completeTransaction = $order->getCompleteTransaction());
        $this->assertNotNull($order->getHeartlandBatchId());
        $this->assertNotNull($paymentAccount = $order->getPaymentAccount());
        $this->assertNotNull($depositAccount = $order->getDepositAccount());
        $this->assertEquals($payment->getPaymentAccount()->getId(), $paymentAccount->getId());
        $this->assertEquals($payment->getDepositAccount()->getId(), $depositAccount->getId());
        $operations = $order->getOperations();
        $this->assertCount(3, $operations);

        $this->assertCount(2, $order->getRentOperations());
        $this->assertNotNull($order->getOtherOperation());
        $this->assertEquals($amount, $order->getTotalAmount());
        $this->assertEquals($rentAmount * 2, $order->getRentAmount());
        $this->assertEquals(25, $order->getOtherAmount());

        $firstRent = $order->getRentOperations()->first();
        $secondRent = $order->getRentOperations()->last();

        // 28 is a min number of days in one month
        $this->assertGreaterThanOrEqual(28, $firstRent->getPaidFor()->diff($secondRent->getPaidFor())->days);

        $this->assertEquals($amount, $contract->getIntegratedBalance());

        $this->getOrderStatusManager()->setComplete($order);

        $contract = $this->getContract($em);
        $this->assertEquals(0, $contract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function collectAndPayBalanceOnlyWhenBalanceLessThanRent()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Contract $contract */
        $contract = $this->getContract($em);
        $rentAmount = $contract->getRent();

        $amount = round($rentAmount / 2);
        $contract->setIntegratedBalance($amount);
        $groupSettings = $contract->getGroup()->getGroupSettings();
        $groupSettings->setPayBalanceOnly(true);
        $groupSettings->setIsIntegrated(true);

        $payment = $this->createPayment($contract, $amount);
        $em->persist($payment);
        $em->persist($contract);
        $em->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommand();

        // "Your Rent is Processing" Email
        $this->assertCount(2, $plugin->getPreSendMessages());

        /** @var OrderSubmerchant $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => $amount));
        $this->assertNotNull($order);
        $this->assertNotNull($order->getHeartlandBatchId());
        $operations = $order->getOperations();
        $this->assertCount(1, $operations);

        $this->assertCount(1, $order->getRentOperations());
        $this->assertNull($order->getOtherOperation());
        $this->assertEquals($amount, $order->getTotalAmount());
        $this->assertEquals($amount, $order->getRentAmount());
        $this->assertEquals(0, $order->getOtherAmount());

        /*
         * TODO: find out correct paid_for values
         * $paidFor = new DateTime('first day of this month');
        $rentOperation = $order->getRentOperations()->first();
        $this->assertContains($paidFor->format('Ymd'), $rentOperation->getPaidFor()->format('Ymd'));*/

        $this->assertEquals($amount, $contract->getIntegratedBalance());

        $this->getOrderStatusManager()->setComplete($order);

        $contract = $this->getContract($em);
        $this->assertEquals(0, $contract->getIntegratedBalance());
    }

    /**
     * @test
     */
    public function completeOrderInstantlyWhenPayingWithCC()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Contract $contract */
        $contract = $this->getContract($em);
        /** @var Payment $payment */
        $payment = $this->createPayment($contract, $contract->getRent());
        $payment->setPaidFor(new DateTime());
        $paymentAccount = $contract->getTenant()->getPaymentAccounts()->filter(
            function ($paymentAccount) {
                if (PaymentAccountType::CARD == $paymentAccount->getType()) {
                    return true;
                }

                return false;
            }
        )->first();
        $payment->setPaymentAccount($paymentAccount);

        $em->persist($payment);
        $em->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommand();

        // "Your Rent is Processing" Email
        $this->assertCount(2, $plugin->getPreSendMessages());

        /** @var OrderSubmerchant $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => $contract->getRent()));
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus());
        $this->assertNotNull($order->getHeartlandBatchId());
    }

    /**
     * @test
     */
    public function closeRecurringPaymentIfPaidWithCreditCardOrderIsFailed()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Contract $contract */
        $contract = $this->getContract($em);
        // Create a payment with negative amount to provoke error
        /** @var Payment $payment */
        $payment = $this->createPayment($contract, '-888', PaymentType::RECURRING);
        $payment->setPaidFor(new DateTime());
        $paymentAccount = $contract->getTenant()->getPaymentAccounts()->filter(
            function ($paymentAccount) {
                if (PaymentAccountType::CARD == $paymentAccount->getType()) {
                    return true;
                }

                return false;
            }
        )->first();
        $payment->setPaymentAccount($paymentAccount);

        $em->persist($payment);
        $em->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommand();

        // "Your Rent is Processing" Email
        $this->assertCount(2, $plugin->getPreSendMessages());

        /** @var OrderSubmerchant $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => '-888'));
        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::ERROR, $order->getStatus());
        // Reload payment from the DB
        $resultPayment = $em->find('RjDataBundle:Payment', $payment->getId());
        $this->assertEquals(PaymentStatus::CLOSE, $resultPayment->getStatus());
        $this->assertCount(2, $resultPayment->getCloseDetails(), 'Payment close details should be an array of 2 items');
        $this->assertContains(PaymentCloseReason::RECURRING_ERROR, $resultPayment->getCloseDetails()['1']);
    }

    protected function prepareFixturesCollectAndPayAciCollectPay(EntityManager $em)
    {
        /* Remove all payments */
        $query = $em->createQuery('DELETE FROM RjDataBundle:Payment');
        $query->execute();
        /** @var Contract $contract */
        $contract = $this->getContract($em);

        /* Prepare Group */
        $contract->getGroup()->getGroupSettings()->setPaymentProcessor(PaymentProcessor::ACI);

        $depositAccount = new DepositAccount($contract->getGroup());
        $depositAccount->setPaymentProcessor($contract->getGroup()->getGroupSettings()->getPaymentProcessor());
        $depositAccount->setType(DepositAccountType::RENT);
        $depositAccount->setMerchantName(564075);

        $contract->getGroup()->addDepositAccount($depositAccount);

        /* Create Payment Accounts */
        /** @var PaymentProcessorAciCollectPay $paymentProcessor */
        $paymentProcessor = $this->getContainer()->get('payment_processor.aci_collect_pay');

        $paymentAccount1 = new PaymentAccount();

        $paymentAccount1->setUser($contract->getTenant());
        $paymentAccount1->setPaymentProcessor(PaymentProcessor::ACI);
        $paymentAccount1->setType(PaymentAccountTypeEnum::BANK);
        $paymentAccount1->setName('Test ACI Bank');
        $paymentAccount1->setBankAccountType(BankAccountType::CHECKING);

        $paymentAccountData = new PaymentAccountData();

        $paymentAccountData->setEntity($paymentAccount1);

        $paymentAccountData
            ->set('account_name', $contract->getTenant()->getFullName())
            ->set('expiration_month', '12')
            ->set('expiration_year', '2025')
            ->set('address_choice', null)
            ->set('card_number', '5110200200001115')
            ->set('routing_number', '063113057')
            ->set('account_number', '123245678')
            ->set('csc_code', '123');

        $paymentAccount2 = clone $paymentAccount1;

        $paymentAccount1->setToken($paymentProcessor->createPaymentToken($paymentAccountData, $contract));

        $this->setOldProfileId(
            md5($contract->getTenant()->getId()),
            $contract->getTenant()->getAciCollectPayProfileId()
        );

        $em->persist($paymentAccount1);

        $paymentAccount2->setType(PaymentAccountTypeEnum::CARD);
        $paymentAccount2->setName('Test ACI Card');
        $paymentAccount2->setBankAccountType(null);

        $paymentAccountData->setEntity($paymentAccount2);

        $paymentAccount2->setToken($paymentProcessor->createPaymentToken($paymentAccountData, $contract));

        $em->persist($paymentAccount2);

        $em->flush();

        return [$paymentAccount1, $paymentAccount2];
    }

    /**
     * @test
     */
    public function collectAndPayAciCollectPay()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        list ($bankPaymentAccount, $cardPaymentAccount) = $this->prepareFixturesCollectAndPayAciCollectPay($em);

        $contract = $this->getContract($em);

        $cardPayment = $this->createPayment($contract, 1001);

        $cardPayment->setPaidFor(new DateTime());
        $cardPayment->setPaymentAccount($cardPaymentAccount);

        $bankPayment = clone $cardPayment;

        $cardPayment->setAmount(-200);

        $bankPayment->setPaymentAccount($bankPaymentAccount);
        // should create another payment for another contract
        $contract2 = $em->getRepository('RjDataBundle:Contract')->findOneBy([
            'status' => ContractStatus::CURRENT,
            'tenant' => $contract->getTenant(),
            'group' => $contract->getGroup(),
        ]);

        $this->assertNotNull($contract2);

        $bankPayment2 = $this->createPayment($contract2, 1002);
        $bankPayment2->setPaidFor(new DateTime());
        $bankPayment2->setPaymentAccount($bankPaymentAccount);

        $em->persist($cardPayment);
        $em->persist($bankPayment);
        $em->persist($bankPayment2);
        $em->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommand(3); // created 3 jobs for 3 payments

        // "Your Rent is Processing" Email
        $this->assertCount(4, $plugin->getPreSendMessages()); // 3 for Order; 1 - Monolog Message

        // Should get 2 Orders with Pending and Error statuses
        /** @var OrderSubmerchant[] $orders */
        $orders = $em->getRepository('DataBundle:Order')->findBy(
            ['paymentProcessor' => PaymentProcessor::ACI],
            ['status' => 'DESC']
        );

        $this->assertCount(3, $orders);

        // first contract bank account
        $this->assertEquals(
            OrderStatus::PENDING,
            $orders[0]->getStatus(),
            $orders[0]->getTransactions()->first()->getMessages()
        );

        // second contract the same account
        $this->assertEquals(
            OrderStatus::PENDING,
            $orders[1]->getStatus(),
            $orders[1]->getTransactions()->first()->getMessages()
        );

        // first contract card account error with minus amount
        $this->assertEquals(OrderStatus::ERROR, $orders[2]->getStatus());

        $this->assertNotEmpty($orders[0]->getTransactions()->first()->getTransactionId());
        $this->assertNotEmpty($orders[1]->getTransactions()->first()->getTransactionId());
        $this->assertNotEmpty($orders[2]->getTransactions()->first()->getMessages());

        $group = $contract->getGroup(); // group is the same
        $date = new \DateTime();
        $expectedBatchId = sprintf('%dB%s', $group->getId(), $date->format('Ymd'));

        $this->assertEquals($expectedBatchId, $orders[0]->getTransactions()->first()->getBatchId());
        $this->assertEquals($expectedBatchId, $orders[1]->getTransactions()->first()->getBatchId());
        $this->assertEquals($expectedBatchId, $orders[2]->getTransactions()->first()->getBatchId());
    }

    /**
     * @param  Contract $contract
     * @param $amount
     * @param  string $type
     * @return Payment
     */
    protected function createPayment(Contract $contract, $amount, $type = PaymentType::ONE_TIME)
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
        $payment->setAmount($amount);
        $payment->setTotal($amount);
        $payment->setType($type);
        $payment->setStatus(PaymentStatus::ACTIVE);
        $payment->setContract($contract);
        $payment->setPaymentAccount($paymentAccount);
        $payment->setDepositAccount($contract->getGroup()->getRentDepositAccountForCurrentPaymentProcessor());
        $today = new DateTime();
        $payment->setDueDate($today->format('j'));
        $payment->setStartMonth($today->format('n'));
        $payment->setStartYear($today->format('Y'));

        return $payment;
    }

    /**
     * @param int $countJobs
     */
    protected function executeCommand($countJobs = 2)
    {
        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount($countJobs, $jobs);

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

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(0, $jobs);
    }

    /**
     * @param EntityManager $em
     * @return Contract
     */
    protected function getContract(EntityManager $em)
    {
        $rentAmount = 987;
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(array('rent' => $rentAmount));
        $this->assertNotNull($contract);

        return $contract;
    }

    /**
     * @param int $profileId
     */
    protected function deleteAciCollectPayProfile($profileId)
    {
        $profile = new Profile();

        $profile->setProfileId($profileId);

        $request = new DeleteProfile($profile);

        $this->getContainer()->get('payum')->getPayment('aci_collect_pay')->execute($request);

        $this->assertTrue($request->getIsSuccessful());

        $this->unsetOldProfileId($profileId);
    }

    protected function tearDown()
    {
        /**
         * Remove all aci profiles
         */
        $profiles = $this->getOldProfileIds();
        if (is_array($profiles) && !empty($profiles)) {
            foreach ($profiles as $profile) {
                if ($profile) {
                    $this->deleteAciCollectPayProfile($profile);
                }
            }
        }
    }
}
