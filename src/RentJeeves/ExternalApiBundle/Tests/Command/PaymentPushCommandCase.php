<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\ExternalApiBundle\Command\PaymentPushCommand;
use RentJeeves\ExternalApiBundle\Tests\Services\MRI\MRIClientCase;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;

class PaymentPushCommandCase extends BaseTestCase
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    public function testDataForSendPaymentToExternalApi()
    {
        return [
            [
                ApiIntegrationType::RESMAN,
                ResManClientCase::RESIDENT_ID,
                ResManClientCase::EXTERNAL_PROPERTY_ID,
                ResManClientCase::EXTERNAL_LEASE_ID
            ],
            [
                ApiIntegrationType::MRI,
                MRIClientCase::RESIDENT_ID,
                MRIClientCase::PROPERTY_ID,
                null
            ]
        ];
    }

    /**
     * @param $apiIntegrationType
     * @param $residentId
     * @param $externalPropertyId
     * @param $externalLeaseId
     *
     * @test
     * @dataProvider testDataForSendPaymentToExternalApi
     */
    public function shouldSendPaymentToExternalApi(
        $apiIntegrationType,
        $residentId,
        $externalPropertyId,
        $externalLeaseId
    ) {
        $this->load(true);
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $this->createTransaction(
            $apiIntegrationType,
            $residentId,
            $externalPropertyId,
            $externalLeaseId
        );

        $jobs = $em->getRepository('RjDataBundle:Job')->findBy(
            ['command' => 'external_api:payment:push']
        );

        $this->assertCount(1, $jobs);

        $job = reset($jobs);

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
