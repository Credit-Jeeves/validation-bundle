<?php

namespace RentJeeves\ExternalApiBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\ExternalApiBundle\Command\YardiReversalReceiptCommand;
use RentJeeves\ExternalApiBundle\Services\Yardi\ReversalReceiptSender;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ReversalReceiptSenderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCollectJobs()
    {
        $this->load(true);
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobs, 'We should not have jobs in fixtures');
        /** @var Holding $holding */
        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding, 'Holding should exist in fixtures');
        $holding->setApiIntegrationType(ApiIntegrationType::YARDI_VOYAGER);
        /** @var Transaction $reversalTransaction */
        $reversalTransaction =  $this->getEntityManager()->getRepository('RjDataBundle:Transaction')->findOneBy(
            ['transactionId' =>'65123261']
        );
        $this->assertNotEmpty($reversalTransaction, 'We should have transaction with this parameters');
        $reversalTransaction->setDepositDate(new \Datetime());
        /** @var Operation $rentOperation */
        $rentOperation = $reversalTransaction->getOrder()->getRentOperations()->first();
        $rentOperation->getContract()->setExternalLeaseId('1234');
        $this->getEntityManager()->flush();
        /** @var ReversalReceiptSender $reversalReceiptSender */
        $reversalReceiptSender = $this->getContainer()->get('yardi.reversal_receipts');
        $reversalReceiptSender->сollectReversalPaymentsToJobsForDate(new \DateTime());
        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(3, $jobs, 'We should create new job');
    }

    /**
     * @test
     */
    public function shouldPushReversedReceipt()
    {
        $this->load(true);
        /** @var Transaction $reversalTransaction */
        $reversalTransaction =  $this->getEntityManager()->getRepository('RjDataBundle:Transaction')->findOneBy(
            [
                'transactionId' =>'65123261',
            ]
        );
        $this->assertNotEmpty($reversalTransaction, 'We should have transaction with this parameters');
        /** @var Operation $rentOperation */
        $rentOperation = $reversalTransaction->getOrder()->getRentOperations()->first();
        $rentOperation->getContract()->setExternalLeaseId('1234');
        $this->getEntityManager()->flush();
        /** @var ReversalReceiptSender $reversalReceiptSender */
        $reversalReceiptSender = $this->getContainer()->get('yardi.reversal_receipts');
        $result = $reversalReceiptSender->pushReversedReceiptByOrderId($reversalTransaction->getOrder()->getId());
        $this->assertTrue($result, 'We not push reversal receipt');
    }
}
