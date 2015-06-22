<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
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
                ApiIntegrationType::RESMAN,
                ResManClientCase::RESIDENT_ID,
                ResManClientCase::EXTERNAL_PROPERTY_ID,
                ResManClientCase::EXTERNAL_LEASE_ID,
                ResManClientCase::EXTERNAL_UNIT_ID
            ],
            [
                ApiIntegrationType::MRI,
                MRIClientCase::RESIDENT_ID,
                MRIClientCase::PROPERTY_ID,
                null,
                null
            ]
        ];
    }

    /**
     * @param string $apiIntegrationType
     * @param string $residentId
     * @param string $externalPropertyId
     * @param string $externalLeaseId
     * @param string $externalUnitId
     *
     * @test
     * @dataProvider dataForSendPaymentToExternalApi
     */
    public function shouldSendPaymentToExternalApi(
        $apiIntegrationType,
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
            $apiIntegrationType,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            $externalUnitId
        );

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertFalse($repo->isOpenedBatch(
            $transaction->getBatchId(),
            $apiIntegrationType,
            $externalPropertyId
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
        $apiIntegrationType = ApiIntegrationType::YARDI_VOYAGER;
        $residentId = PaymentClientCase::RESIDENT_ID;
        $externalPropertyId = PaymentClientCase::PROPERTY_ID;
        $externalLeaseId = PaymentClientCase::RESIDENT_ID;
        $externalUnitId = null;

        $this->load(true);
        $em = $this->getEntityManager();

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotEmpty($holding);
        $holding->getYardiSettings()->setSynchronizationStrategy(SynchronizationStrategy::REAL_TIME);
        $em->flush($holding->getYardiSettings());
        $em->flush($holding);
        $this->assertCount(0, $jobs);
        $transaction = $this->createTransaction(
            $apiIntegrationType,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            null
        );

        /** @var PaymentBatchMappingRepository $repo */
        $repo = $em->getRepository('RjDataBundle:PaymentBatchMapping');

        $this->assertFalse($repo->isOpenedBatch(
            $transaction->getBatchId(),
            $apiIntegrationType,
            $externalPropertyId
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
}
