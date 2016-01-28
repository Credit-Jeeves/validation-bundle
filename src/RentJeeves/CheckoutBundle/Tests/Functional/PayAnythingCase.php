<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Command\PayCommand;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PayAnythingCase extends BaseTestCase
{
    /**
     * @test
     * @return int
     */
    public function shouldCreateCustomPayment()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        /** @var Payment[] $activePayments */
        $activePayments = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Payment')
            ->findBy(['status' => 'active']);
        $beforeCountActivePayments = count($activePayments);
        /** @var Group $group */
        $group = $this->getEntityManager()
            ->getRepository('DataBundle:Group')
            ->findOneBy(['name' => 'Test Rent Group']);

        $this->assertNotEmpty($group, 'Check fixtures, group with name "Test Rent Group" should be exist');

        $group->getGroupSettings()->setAllowPayAnything(true);
        $this->getEntityManager()->flush($group->getGroupSettings());

        $this->login('tenant11@example.com', 'pass');

        $this->page->clickLink('pay-anything-2');

        $this->session->wait($this->timeout, '$("#pay-anything-popup:visible").length');

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull(
            $nextBtn = $this->page
                ->find('css', '#pay-anything-popup button span:contains("pay_popup.step.next")')
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-anything-popup .attention-box li'));
        $this->assertCount(3, $errors, 'Should get 3 errors');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_payanything_paymenttype');

        $startDate = (new \DateTime('+ 1 day'))->format('n/j/Y');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_payanything_paymenttype_payFor' => DepositAccountType::APPLICATION_FEE,
                'rentjeeves_checkoutbundle_payanything_paymenttype_start_date' => $startDate,
                'rentjeeves_checkoutbundle_payanything_paymenttype_amount' => 100.45,
            ]
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-anything-popup .attention-box li'));
        $this->assertCount(0, $errors, 'Should get no error');

        $existingPaymentSource = $this->page->find(
            'css',
            '#pay-anything-popup .payment-accounts label:nth-of-type(1)'
        );
        $this->assertNotNull($existingPaymentSource, 'Should be present payment source');
        $existingPaymentSource->click();

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-anything-popup .attention-box li'));
        $this->assertCount(0, $errors, 'Should get no error');

        $this->assertNotNull(
            $nextBtn = $this->page
                ->find('css', '#pay-anything-popup button span:contains("checkout.make_payment")')
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull(
            $nextBtn = $this->page
                ->find('css', '#pay-anything-popup button span:contains("pay_popup.close")')
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '(document.readyState == "complete")'); // wait reload page

        $this->assertNotNull(
            $dashboardPayments = $this->page->findAll('css', 'div.no-rent-scheduled-payment'),
            'Should be present scheduled payment on dashboard'
        );

        $this->assertCount(1, $dashboardPayments);

        /** @var Payment[] $activePayments */
        $activePayments = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Payment')
            ->findBy(['status' => 'active']);
        $this->assertCount($beforeCountActivePayments + 1, $activePayments, 'Should be create one new active payment');
        /** @var Payment $customPayment */
        $customPayment = end($activePayments);

        $this->assertEquals(
            DepositAccountType::APPLICATION_FEE,
            $customPayment->getDepositAccount()->getType(),
            'Deposit Account should be application_fee'
        );

        $this->assertEquals(
            number_format(100.45, 2),
            number_format($customPayment->getAmount(), 2),
            'Amount should be 100.45'
        );

        $this->assertEquals($customPayment->getAmount(), $customPayment->getTotal(), 'Total should be equal amount');

        $this->assertEquals(
            $startDate,
            $customPayment->getStartDate()->format('n/j/Y'),
            'Start date should be ' . $startDate
        );

        return $customPayment->getId();
    }

    /**
     * @param int $customPaymentId
     *
     * @test
     * @depends shouldCreateCustomPayment
     */
    public function shouldCancelCustomPayment($customPaymentId)
    {
        /** @var Payment $activePayment */
        $activePayment = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Payment')
            ->findOneBy(['id' => $customPaymentId, 'status' => 'active']);

        $this->assertNotEmpty($activePayment, 'Custom payment was not be created');

        $this->assertNotNull(
            $cancelLink = $this->page->find('css', '#pay-anything-cancel-' . $customPaymentId),
            'Cancel link not found'
        );

        $cancelLink->click();

        $this->session->wait($this->timeout, '$("#payment-account-cancel:visible").length');

        $this->assertNotNull(
            $yesBtn = $this->page
                ->find('css', '#payment-account-cancel a>span:contains("checkout.payment.cancel.yes")')
        );

        $yesBtn->click();

        $this->session->wait($this->timeout, '(document.readyState == "complete")'); // wait reload page

        $this->assertNull(
            $cancelLink = $this->page->find('css', '#pay-anything-cancel-' . $customPaymentId),
            'Payment should be canceled'
        );

        /** @var Payment $activePayment */
        $activePayment = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Payment')
            ->findOneBy(['id' => $customPaymentId, 'status' => 'active']);

        $this->assertEmpty($activePayment, 'Custom payment should be canceled');
    }

    /**
     * @test
     */
    public function createAndExecuteCustomPayment()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        /** @var Payment[] $activePayments */
        $activePayments = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Payment')
            ->findBy(['status' => 'active']);
        $beforeCountActivePayments = count($activePayments);
        /** @var Group $group */
        $group = $this->getEntityManager()
            ->getRepository('DataBundle:Group')
            ->findOneBy(['name' => 'Test Rent Group']);

        $this->assertNotEmpty($group, 'Check fixtures, group with name "Test Rent Group" should be exist');

        $group->getGroupSettings()->setAllowPayAnything(true);
        $this->getEntityManager()->flush($group->getGroupSettings());

        $this->login('tenant11@example.com', 'pass');

        $this->page->clickLink('pay-anything-2');

        $this->session->wait($this->timeout, '$("#pay-anything-popup:visible").length');

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_payanything_paymenttype');

        $startDate = (new \DateTime('+ 1 day'))->format('n/j/Y');

        $this->fillForm(
            $form,
            [
                'rentjeeves_checkoutbundle_payanything_paymenttype_payFor' => DepositAccountType::APPLICATION_FEE,
                'rentjeeves_checkoutbundle_payanything_paymenttype_start_date' => $startDate,
                'rentjeeves_checkoutbundle_payanything_paymenttype_amount' => 100.45,
            ]
        );

        $this->assertNotNull(
            $nextBtn = $this->page
                ->find('css', '#pay-anything-popup button span:contains("pay_popup.step.next")')
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-anything-popup .attention-box li'));
        $this->assertCount(0, $errors, 'Should get no error');

        $existingPaymentSource = $this->page->find(
            'css',
            '#pay-anything-popup .payment-accounts label:nth-of-type(1)'
        );

        $this->assertNotNull($existingPaymentSource, 'Should be present payment source');
        $existingPaymentSource->click();

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        $this->assertNotNull($errors = $this->page->findAll('css', '#pay-anything-popup .attention-box li'));
        $this->assertCount(0, $errors, 'Should get no error');

        $this->assertNotNull(
            $nextBtn = $this->page
                ->find('css', '#pay-anything-popup button span:contains("checkout.make_payment")')
        );

        $nextBtn->click();

        $this->session->wait($this->timeout, '$("#pay-anything-popup>div.overlay").is(":visible")');
        $this->session->wait($this->timeout, '!$("#pay-anything-popup>div.overlay").is(":visible")');

        /** @var Payment[] $activePayments */
        $activePayments = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Payment')
            ->findBy(['status' => 'active']);
        $this->assertCount($beforeCountActivePayments + 1, $activePayments, 'Should be create one new active payment');
        /** @var Payment $customPayment */
        $customPayment = end($activePayments);

        $this->assertEquals(
            DepositAccountType::APPLICATION_FEE,
            $customPayment->getDepositAccount()->getType(),
            'Deposit Account should be application_fee'
        );

        $this->assertEquals(
            number_format(100.45, 2),
            number_format($customPayment->getAmount(), 2),
            'Amount should be 100.45'
        );

        $this->assertEquals($customPayment->getAmount(), $customPayment->getTotal(), 'Total should be equal amount');

        $this->assertEquals(
            $startDate,
            $customPayment->getStartDate()->format('n/j/Y'),
            'Start date should be ' . $startDate
        );

        $customPayment->setStartDate(); // set today for processing

        $this->getEntityManager()->flush($customPayment);

        $emailPlugin = $this->registerEmailListener();
        $emailPlugin->clean();

        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $this->assertCount(2, $jobs, sprintf('Should collect 2 payments (1 from fixtures)'));

        $commandTester = $this->executePayCommand(end($jobs)->getId());

        $this->assertRegExp("/Start\nOK/", $commandTester->getDisplay());
        $this->assertCount(1, $emailPlugin->getPreSendMessages());
        $this->assertEquals('RentTrack Payment Receipt', $emailPlugin->getPreSendMessage(0)->getSubject());

        $emailPlugin->clean();

        /** @var OrderSubmerchant $order */
        $order = $this->getEntityManager()->getRepository('DataBundle:Order')->findOneBy(['sum' => 100.45]);
        $this->assertNotNull($order, 'Should be created order');
        $this->assertNotEquals($order->getStatus(), OrderStatus::ERROR, "Order should not have error status");
        $this->assertNotNull(
            $completeTransaction = $order->getCompleteTransaction(),
            'Order should have complete transaction'
        );
        $this->assertNotNull(
            $order->getTransactionBatchId(),
            'Order should have transaction batch id'
        );
        $this->assertNotNull(
            $paymentAccount = $order->getPaymentAccount(),
            'Order should be set payment account'
        );
        $this->assertNotNull(
            $depositAccount = $order->getDepositAccount(),
            'Order should be set deposit account '
        );
        $this->assertEquals(
            $customPayment->getPaymentAccount()->getId(),
            $paymentAccount->getId(),
            'Payment account should be the same'
        );
        $this->assertEquals(
            $customPayment->getDepositAccount()->getId(),
            $depositAccount->getId(),
            'Deposit account should be the same'
        );
        $this->assertNotNull($order->getCustomOperation(), 'Order should have custom operation');
        $this->assertEquals(
            $order->getTotalAmount(),
            $order->getOtherAmount(),
            'Sum of order should be included just other amount'
        );
    }

    /**
     * @param $jobId
     * @return CommandTester
     */
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
}
