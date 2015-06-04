<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\ExternalApiBundle\Command\CloseBatchCommand;
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
            ],
            [
                ApiIntegrationType::YARDI_VOYAGER,
                PaymentClientCase::RESIDENT_ID,
                PaymentClientCase::PROPERTY_ID,
                PaymentClientCase::RESIDENT_ID,
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
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $numberJobs = count($jobs);
        $transaction = $this->createTransaction(
            $apiIntegrationType,
            $residentId,
            $externalPropertyId,
            $externalLeaseId,
            $externalUnitId
        );
        $numberJobs++;
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

        $this->assertCount($numberJobs, $jobs);

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
