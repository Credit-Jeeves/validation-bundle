<?php
namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Order;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\TestBundle\Command\BaseTestCase;

class PaymentCommandsCase extends BaseTestCase
{
    /**
     * test
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
    public function collectAndPayBalanceOnly()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $rentAmount = 987;
        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy(array('rent' => $rentAmount));
        $this->assertNotNull($contract);

        $tenant = $contract->getTenant();
        $paymentAccount = $tenant->getPaymentAccounts()->first();
        $amount = 1999;
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

        $groupSettings = $contract->getGroup()->getGroupSettings();
        $groupSettings->setPayBalanceOnly(true);
        $groupSettings->setIsIntegrated(true);

        $em->persist($payment);
        $em->flush();

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

        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(array('sum' => $amount));
        $this->assertNotNull($order);
        $operations = $order->getOperations();
        $this->assertCount(3, $operations);

        $this->assertCount(2, $order->getRentOperations());
        $this->assertNotNull($order->getOtherOperation());
        $this->assertEquals($amount, $order->getTotalAmount());
        $this->assertEquals($rentAmount * 2, $order->getRentAmount());
        $this->assertEquals($amount - ($rentAmount * 2), $order->getOtherAmount());
        $paidFor = new DateTime('first day of this month');
        $expectedPaidForDates = array($paidFor->format('Ymd'), $paidFor->modify('+1 month')->format('Ymd'));
        $firstRent = $order->getRentOperations()->first();
        $secondRent = $order->getRentOperations()->last();
        $this->assertContains($firstRent->getPaidFor()->format('Ymd'), $expectedPaidForDates);
        $this->assertContains($secondRent->getPaidFor()->format('Ymd'), $expectedPaidForDates);
        // 28 is a min number of days in one month
        $this->assertGreaterThanOrEqual(28, $firstRent->getPaidFor()->diff($secondRent->getPaidFor())->days);
    }
}
