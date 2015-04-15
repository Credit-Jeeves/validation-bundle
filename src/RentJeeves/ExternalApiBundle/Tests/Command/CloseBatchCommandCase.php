<?php

namespace RentJeeves\ExternalApiBundle\Tests\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentBatchStatus;
use RentJeeves\ExternalApiBundle\Command\CloseBatchCommand;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Command\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CloseBatchCommandCase extends BaseTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaymentBatchMapping
     */
    protected $batchMapping;

    const BATCH_ID = '111555';

    public function setUp()
    {
        $this->load(true);
        /** @var $resManClient ResManClient */
        $resManClient = $this->getContainer()->get('resman.client');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);

        $batchId = $resManClient->openBatch(ResManClientCase::EXTERNAL_PROPERTY_ID, new \DateTime());

        $this->batchMapping = new PaymentBatchMapping();
        $this->batchMapping->setExternalPropertyId(ResManClientCase::EXTERNAL_PROPERTY_ID);
        $this->batchMapping->setAccountingPackageType(ApiIntegrationType::RESMAN);
        $this->batchMapping->setPaymentBatchId(self::BATCH_ID);
        $this->batchMapping->setAccountingBatchId($batchId);

        /** @var TransactionRepository $repo */
        $repo = $this->em->getRepository('RjDataBundle:Transaction');

        $holding = $repo->getMerchantHoldingByBatchId(self::BATCH_ID);

        $holding->getAccountingSettings()->setApiIntegration(ApiIntegrationType::RESMAN);

        $this->em->persist($this->batchMapping);
        $this->em->persist($holding);

        $this->em->flush();
    }


    /**
     * @test
     */
    public function shouldCloseAllResmanBatches()
    {
        $application = new Application($this->getKernel());
        $application->add(new CloseBatchCommand());

        $command = $application->find('api:accounting:close-payment-batches');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->em->refresh($this->batchMapping);

        $this->assertNotEmpty($this->batchMapping->getClosedAt());
        $this->assertEquals(PaymentBatchStatus::CLOSED, $this->batchMapping->getStatus());
    }
}
