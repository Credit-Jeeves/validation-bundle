<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use JMS\JobQueueBundle\Command\RunCommand;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use RentJeeves\DataBundle\Enum\SynchronizationStrategy;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\ExternalApiBundle\Command\PaymentPushCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\MRI\MRIClientCase;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\PaymentClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;

class PaymentPushCommandCase extends BaseTestCase
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    public function dataForSendPaymentToExternalApi()
    {
        return [
            [
                AccountingSystem::RESMAN,
                ResManClientCase::RESIDENT_ID,
                ResManClientCase::EXTERNAL_PROPERTY_ID,
                ResManClientCase::EXTERNAL_LEASE_ID,
                ResManClientCase::EXTERNAL_UNIT_ID
            ],
            [
                AccountingSystem::MRI,
                MRIClientCase::RESIDENT_ID,
                MRIClientCase::PROPERTY_ID,
                null,
                null
            ]
        ];
    }

    /**
     * @param string $accountingSystem
     * @param string $residentId
     * @param string $externalPropertyId
     * @param string $externalLeaseId
     * @param string $externalUnitId
     *
     * @test
     * @dataProvider dataForSendPaymentToExternalApi
     */
    public function shouldSendPaymentToExternalApi(
        $accountingSystem,
        $residentId,
        $externalPropertyId,
        $externalLeaseId,
        $externalUnitId
    ) {
        $this->load(true);
        $em = $this->getEntityManager();

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $this->assertCount(0, $jobs);
        $transaction = $this->createTransaction(
            $accountingSystem,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            $externalUnitId
        );

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertFalse($repo->isOpenedBatch(
            $transaction->getBatchId(),
            $accountingSystem
        ));

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $this->assertCount(1, $jobs);

        $job = end($jobs);

        $application = new Application($this->getKernel());
        $application->add(new PaymentPushCommand());

        $command = $application->find('external_api:payment:push');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'       => $command->getName(),
                '--jms-job-id'  => $job->getId(),
            ]
        );
        $this->assertRegExp("/Start\nSuccess/", $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function shouldSendPaymentToYardiApi()
    {
        $accountingSystem = AccountingSystem::YARDI_VOYAGER;
        $residentId = PaymentClientCase::RESIDENT_ID;
        $externalPropertyId = PaymentClientCase::PROPERTY_ID;
        $externalLeaseId = PaymentClientCase::RESIDENT_ID;
        $externalUnitId = null;

        $this->load(true);
        $em = $this->getEntityManager();

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );
        $this->assertCount(0, $jobs);

        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $holding->getYardiSettings()->setSynchronizationStrategy(SynchronizationStrategy::REAL_TIME);
        $em->flush($holding->getYardiSettings());

        $transaction = $this->createTransaction(
            $accountingSystem,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            null
        );

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertFalse($repo->isOpenedBatch(
            $transaction->getBatchId(),
            $accountingSystem
        ));

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $this->assertCount(1, $jobs);

        $job = end($jobs);

        $application = new Application($this->getKernel());
        $application->add(new PaymentPushCommand());

        $command = $application->find('external_api:payment:push');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'       => $command->getName(),
                '--jms-job-id'  => $job->getId(),
            ]
        );
        $this->assertRegExp("/Start\nSuccess/", $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function shouldOpenOnlyOneBatch()
    {
        $accountingSystem = AccountingSystem::YARDI_VOYAGER;
        $residentId = PaymentClientCase::RESIDENT_ID;
        $externalPropertyId = PaymentClientCase::PROPERTY_ID;
        $externalLeaseId = PaymentClientCase::RESIDENT_ID;
        $externalUnitId = null;

        $this->load(true);
        $em = $this->getEntityManager();

        $em->getConnection()->exec(
            'UPDATE jms_jobs SET state="finished";'
        );

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );
        $this->assertCount(0, $jobs);

        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $holding->getYardiSettings()->setSynchronizationStrategy(SynchronizationStrategy::REAL_TIME);
        $em->flush($holding->getYardiSettings());

        $transaction = $this->createTransaction(
            $accountingSystem,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            null
        );

        $transaction2 = $this->createTransaction(
            $accountingSystem,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            null
        );

        $this->assertEquals($transaction->getBatchId(), $transaction2->getBatchId());

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertFalse($repo->isOpenedBatch(
            $transaction->getBatchId(),
            $accountingSystem
        ));

        /** @var Job[] $jobs */
        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $this->assertCount(2, $jobs);

        $application = new Application($this->getKernel());
        $application->add(new RunCommand());

        $command = $application->find('jms-job-queue:run');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'       => $command->getName(),
                '--max-runtime'  => 5,
            ]
        );

        $em->refresh($jobs[0]);
        $em->refresh($jobs[1]);

        $this->assertEquals(Job::STATE_FINISHED, $jobs[0]->getState());
        $this->assertEquals(Job::STATE_FINISHED, $jobs[1]->getState());

        $this->assertTrue($repo->isOpenedBatch(
            $transaction->getBatchId(),
            $accountingSystem
        ));

        $batches = $repo->createQueryBuilder('pbm')
            ->select('pbm.accountingBatchId')
            ->where('pbm.paymentBatchId = :paymentBatchId')
            ->andWhere('pbm.accountingPackageType = :accountingPackageType')
            ->andWhere('pbm.externalPropertyId = :externalPropertyId')
            ->andWhere('pbm.status = :status')
            ->setParameters([
                'paymentBatchId' => $transaction->getBatchId(),
                'accountingPackageType' => $accountingSystem,
                'externalPropertyId' => $externalPropertyId,
                'status' => PaymentBatchStatus::OPENED,
            ])
            ->getQuery()
            ->getResult();

        $this->assertCount(1, $batches);
    }
}
