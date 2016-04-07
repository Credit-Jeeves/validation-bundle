<?php

namespace RentJeeves\CheckoutBundle\Tests\Command;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Command\ProfitStarsReportSynchronizeCommand;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Entity\ProfitStarsTransaction;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\TestBundle\Command\BaseTestCase;

class ProfitStarsReportSynchronizeCommandCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSyncReportsFromProfitStars()
    {
        $this->load(true);
        // Fixtures for tests
        $holding = $this->getEntityManager()->find('DataBundle:Holding', 5);

        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setMerchantId('test');
        $profitStarsSettings->setHolding($holding);
        $this->getEntityManager()->persist($profitStarsSettings);

        $group = $this->getEntityManager()->find('DataBundle:Group', 24);

        $depositAccount = new DepositAccount();
        $depositAccount->setHolding($holding);
        $depositAccount->setGroup($group);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        $depositAccount->setMerchantName('test');
        $depositAccount->setMid(1);
        $this->getEntityManager()->persist($depositAccount);
        // Fixtures for settled event "report15"
        $transaction = $this->getEntityManager()->find('RjDataBundle:Transaction', 1);
        $transaction->setTransactionId('7VC825CFBA2');
        $transaction->setDepositDate(null);
        $profitStarsTransaction = new ProfitStarsTransaction();
        $profitStarsTransaction->setOrder($transaction->getOrder());
        $profitStarsTransaction->setTransactionNumber('');
        $profitStarsTransaction->setItemId(100);
        $this->getEntityManager()->persist($profitStarsTransaction);
        // Fixtures for Declined event "report4"
        $orderForReversal = $this->getEntityManager()->find('DataBundle:Order', 3);
        $newProfitStarsTransaction = new ProfitStarsTransaction();
        $newProfitStarsTransaction->setOrder($orderForReversal);
        $newProfitStarsTransaction->setTransactionNumber('{37e9b6b9-4058-4ac6-aa76-51f9bb67badc}');
        $newProfitStarsTransaction->setItemId(111);
        $this->getEntityManager()->persist($newProfitStarsTransaction);

        $this->getEntityManager()->flush();

        $this->assertNull(
            $transaction->getOrder()->getProfitStarsTransaction(),
            'Order should be not related with ProfitStars transaction.'
        );
        $allTransaction = $this->getEntityManager()
            ->getRepository('RjDataBundle:Transaction')->findAll();

        $this->executeCommandTester(new ProfitStarsReportSynchronizeCommand());

        $transaction = $this->getEntityManager()->find('RjDataBundle:Transaction', 1);

        $this->assertNotNull($transaction->getDepositDate(), 'Deposit date is not updated.');
        $this->assertNotEquals(
            '',
            $transaction->getOrder()->getProfitStarsTransaction()->getTransactionNumber(),
            'TransactionNumber for ProfitStarsTransaction is not updated.'
        );
        $allTransactionAfterSync = $this->getEntityManager()
            ->getRepository('RjDataBundle:Transaction')->findAll();

        $this->assertEquals(
            count($allTransaction) + 1,
            count($allTransactionAfterSync),
            'New transaction is not created.'
        );
        /** @var Transaction $lastTransaction */
        $lastTransaction = end($allTransactionAfterSync);
        $this->assertEquals(
            TransactionStatus::REVERSED,
            $lastTransaction->getStatus(),
            'Incorrect status for new transaction.'
        );
        $this->assertEquals(
            'ZMC825CFBA2',
            $lastTransaction->getTransactionId(),
            'Incorrect TransactionId for new transaction.'
        );
        $this->assertEquals(
            3,
            $lastTransaction->getOrder()->getId(),
            'Incorrect Order for new transaction.'
        );
        $this->assertEquals(
            OrderStatus::RETURNED,
            $lastTransaction->getOrder()->getStatus(),
            'Status for reversal order is not updated.'
        );
    }
}
