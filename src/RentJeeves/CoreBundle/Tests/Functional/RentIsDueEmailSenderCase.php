<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CoreBundle\Services\Emails\TenantMailer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class RentIsDueEmailSenderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldFindContractsAndSendPaymentDueEmailSayingNoPaymentSetUp()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(
            1,
            $plugin->getPreSendMessages(),
            'We should send one email for contract which we preparing.'
        );

        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Is Due', $message->getSubject(), 'We send not correct email');

    }

    /**
     * @test
     */
    public function shouldNotSendBecauseDoNotHaveOperationsWithin3months()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $contract->getOperations()->clear();
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(
            0,
            $plugin->getPreSendMessages(),
            'We should NOT send email for contract.'
        );
    }

    /**
     * @test
     */
    public function shouldFindContractsAndSendPaymentDueSayingYouHaveRecurringPaymentSetUpWithExecutionDateOf()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        /** @var DepositAccount $depositAccount */
        $depositAccount = $this->getEntityManager()->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            [
                'holding' => $contract->getHolding(),
                'type' => DepositAccountType::RENT
            ]
        );
        $this->assertNotEmpty($depositAccount, 'Deposit account should exist in fixtures');
        $payment = $depositAccount->getPayments()->first();
        $this->assertNotEmpty($payment, 'Payment should exist in fixtures');
        $payment->setType(PaymentType::RECURRING);
        $contract->addPayment($payment);
        $contract->setFinishAt(new \DateTime('+2 years'));
        $this->getEntityManager()->persist($depositAccount);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(
            1,
            $plugin->getPreSendMessages(),
            'We should send one email for contract which we preparing.'
        );

        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Is Due', $message->getSubject(), 'We send not correct email');

    }

    /**
     * @test
     */
    public function shouldSendEmailSayingOneTimePaymentWithExecutionDateOf()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        /** @var DepositAccount $depositAccount */
        $depositAccount = $this->getEntityManager()->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            [
                'holding' => $contract->getHolding(),
                'type' => DepositAccountType::RENT
            ]
        );
        $this->assertNotEmpty($depositAccount, 'Deposit account should exist in fixtures');
        /** @var Payment $payment */
        $payment = $depositAccount->getPayments()->first();
        $this->assertNotEmpty($payment, 'Payment should exist in fixtures');
        $payment->setType(PaymentType::ONE_TIME);
        $contract->addPayment($payment);
        $contract->setFinishAt(new DateTime('+1 year'));
        $this->getEntityManager()->persist($depositAccount);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(
            1,
            $plugin->getPreSendMessages(),
            'We should send one email for contract which we preparing.'
        );
        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Is Due', $message->getSubject(), 'We send not correct email');
    }

    /**
     * @test
     */
    public function shouldCheckEmailThatSaysYourRecurringPaymentHasEnded()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        $contract->setFinishAt(new DateTime('+1 year'));
        /** @var DepositAccount $depositAccount */
        $depositAccount = $this->getEntityManager()->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            [
                'holding' => $contract->getHolding(),
                'type' => DepositAccountType::RENT
            ]
        );
        $this->assertNotEmpty($depositAccount, 'Deposit account should exist in fixtures');
        /** @var Payment $payment */
        $payment = $depositAccount->getPayments()->first();
        $this->assertNotEmpty($payment, 'Payment should exist in fixtures');
        $payment->setType(PaymentType::RECURRING);
        $payment->setEndDate('-9 month');
        $contract->addPayment($payment);
        $this->getEntityManager()->persist($depositAccount);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(
            1,
            $plugin->getPreSendMessages(),
            'We should send one email for contract which we preparing.'
        );

        $message = $plugin->getPreSendMessage(0);
        $this->assertEquals('Your Rent Is Due', $message->getSubject(), 'We send not correct email');
    }

    /**
     * @test
     */
    public function shouldNotSendBecauseNextDueDateIsPastFinishAt()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        /** @var DepositAccount $depositAccount */
        $depositAccount = $this->getEntityManager()->getRepository('RjDataBundle:DepositAccount')->findOneBy(
            [
                'holding' => $contract->getHolding(),
                'type' => DepositAccountType::RENT
            ]
        );
        $this->assertNotEmpty($depositAccount, 'Deposit account should exist in fixtures');
        /** @var Payment $payment */
        $payment = $depositAccount->getPayments()->first();
        $this->assertNotEmpty($payment, 'Payment should exist in fixtures');
        $payment->setType(PaymentType::RECURRING);
        $contract->addPayment($payment);
        $contract->setFinishAt(new DateTime('-10 month'));
        $this->getEntityManager()->persist($depositAccount);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(0, $plugin->getPreSendMessages(), 'We should NOT send email.');
    }

    /**
     * @test
     */
    public function shouldNotSendEmailWhenUserDisableEmailNotify()
    {
        $this->load(true);
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findOneBy(
            ['externalLeaseId' => 't0012020']
        );
        $this->assertNotEmpty($contract, 'We should have this contract in fixtures');
        $contract->setStatus(ContractStatus::APPROVED);
        $contract->getTenant()->setEmailNotification(false);
        $today = new DateTime('now');
        $today->modify('+4 days');
        $contract->setDueDate($today->format('d'));
        $this->getEntityManager()->flush();

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        /** @var TenantMailer $tenantMailer */
        $tenantMailer = $this->getContainer()->get('rent.is_due.email_sender');
        $tenantMailer->modifyShiftedDate('+4 days');
        $tenantMailer->findContractsAndSendPaymentDueEmails();

        $this->assertCount(
            0,
            $plugin->getPreSendMessages(),
            'We should NOT send any email for contract which we preparing.'
        );
    }
}
