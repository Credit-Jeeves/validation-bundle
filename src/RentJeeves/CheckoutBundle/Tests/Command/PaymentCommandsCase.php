<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Job;
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
    /**
     * @test
     */
    public function collectAndPay()
    {
        $this->load(true);
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(1, $jobs);

        $command = $application->find('payment:pay');
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--jms-job-id' => $jobs[0]->getId(),
            )
        );
        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());
        // "Your Rent is Processing" Email
        $this->assertCount(1, $plugin->getPreSendMessages());

        $plugin->clean();
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--jms-job-id' => $jobs[0]->getId(),
            )
        );
        $this->assertRegExp("/Start\nPayment already executed./", $commandTester->getDisplay());
        $this->assertCount(0, $plugin->getPreSendMessages());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(0, $jobs);
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
        $em->flush();

        $this->executeCommand();

        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => $amount));
        $this->assertNotNull($order);
        $this->assertNotNull($order->getHeartlandBatchId());
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

        $order->setStatus(OrderStatus::COMPLETE);
        $em->flush($order);
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

        $this->executeCommand();

        /** @var Order $order */
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

        $order->setStatus(OrderStatus::COMPLETE);
        $em->flush($order);
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

        $this->executeCommand();

        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => $contract->getRent()));
        $this->assertNotNull($order);
        $this->assertNotNull($order->getHeartlandBatchId());
        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus());
    }

    protected function createPayment(Contract $contract, $amount)
    {
        $tenant = $contract->getTenant();
        $paymentAccount = $tenant->getPaymentAccounts()->first();

        $payment = new Payment();
        $payment->setAmount($amount);
        $payment->setTotal($amount);
        $payment->setType(PaymentType::ONE_TIME);
        $payment->setStatus(PaymentStatus::ACTIVE);
        $payment->setContract($contract);
        $payment->setPaymentAccount($paymentAccount);
        $today = new DateTime();
        $payment->setDueDate($today->format('j'));
        $payment->setStartMonth($today->format('n'));
        $payment->setStartYear($today->format('Y'));

        return $payment;
    }

    protected function executeCommand()
    {
        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $application = new Application($this->getKernel());
        $application->add(new PayCommand());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(2, $jobs);

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

        // "Your Rent is Processing" Email
        $this->assertCount(2, $plugin->getPreSendMessages());

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(0, $jobs);
    }

    /**
     * @param $em
     * @return array
     */
    protected function getContract($em)
    {
        $rentAmount = 987;
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(array('rent' => $rentAmount));
        $this->assertNotNull($contract);

        return $contract;
    }
}
