<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use RentJeeves\ExternalApiBundle\Command\CloseBatchCommand;
use RentJeeves\ExternalApiBundle\Services\ExternalApiClientFactory;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\PaymentClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CloseBatchCommandCase extends BaseTestCase
{
    const BATCH_ID = '111555';

    /**
     * @return array
     */
    public function providerShouldCloseAllBatches()
    {
        return [
            [ApiIntegrationType::RESMAN, ResManClientCase::EXTERNAL_PROPERTY_ID],
            [ApiIntegrationType::YARDI_VOYAGER, PaymentClientCase::PROPERTY_ID]
        ];
    }

    /**
     * @test
     * @dataProvider providerShouldCloseAllBatches
     */
    public function shouldCloseAllBatches($accountType, $externalPropertyId)
    {
        $this->load(true);
        $em = $this->getEntityManager();
        /** @var ExternalApiClientFactory $factoryClient */
        $factoryClient = $this->getContainer()->get('accounting.api_client.factory');
        $batchMapping = new PaymentBatchMapping();
        $batchMapping->setExternalPropertyId($externalPropertyId);
        $batchMapping->setAccountingPackageType($accountType);
        $batchMapping->setPaymentBatchId(self::BATCH_ID);

        /** @var TransactionRepository $repo */
        $repo = $em->getRepository('RjDataBundle:Transaction');
        $holding = $repo->getMerchantHoldingByBatchId(self::BATCH_ID);

        $holding->setApiIntegrationType($accountType);

        $em->persist($batchMapping);

        $client = $factoryClient->createClient($accountType, $holding->getExternalSettings());
        $batchId = $client->openBatch($externalPropertyId, new \DateTime(), 'Just are test');
        $batchMapping->setAccountingBatchId($batchId);

        $em->flush();

        $application = new Application($this->getKernel());
        $application->add(new CloseBatchCommand());

        $command = $application->find('api:accounting:close-payment-batches');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $em->refresh($batchMapping);

        $this->assertNotEmpty($batchMapping->getClosedAt());
        $this->assertEquals(PaymentBatchStatus::CLOSED, $batchMapping->getStatus());
    }
}
