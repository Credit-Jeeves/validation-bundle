<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\PaymentClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Messages;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Tests\Services\Yardi\Clients\BaseClientCase as Base;

class PaymentClientCase extends Base
{
    const PROPERTY_ID = 'rnttrk01';

    const RESIDENT_ID = 't0012027';

    protected static $batchId;

    /**
     * @var $client PaymentClient
     */
    protected static $client;

    protected function initOpenReceiptBatchDepositDate()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        self::$client = $clientFactory->getClient(
            $this->getYardiSettings(),
            SoapClientEnum::YARDI_PAYMENT
        );

        self::$batchId = self::$client->openReceiptBatchDepositDate(
            new DateTime(),
            self::PROPERTY_ID,
            $description = 'Test open date'
        );
    }

    protected function checkError()
    {
        if (self::$client->isError()) {
            $this->assertFalse(true, self::$client->getErrorMessage());
        }
    }

    /**
     * @test
     */
    public function openReceiptBatchDepositDate()
    {
        $this->initOpenReceiptBatchDepositDate();
        $this->checkError();
    }

    /**
     * @test
     * @depends openReceiptBatchDepositDate
     */
    public function addReceiptsToBatch()
    {
        $kernel = $this->getKernel();
        $path = $kernel->locateResource(
            '@ExternalApiBundle/Resources/fixtures/receipt_push_sample.xml'
        );
        $xml = file_get_contents($path);
        $xml = str_replace(['%residentId%', '%propertyId%'], [self::RESIDENT_ID, self::PROPERTY_ID], $xml);
        /**
         * @var $result Messages
         */
        $result = self::$client->addReceiptsToBatch(
            self::$batchId,
            $xml
        );

        $this->checkError();
        $this->assertEquals('2 Receipts were added to Batch '.self::$batchId, $result->getMessage()->getMessage());
    }

    /**
     * @test
     * @depends addReceiptsToBatch
     */
    public function closeReceiptBatch()
    {
        $result = self::$client->closeReceiptBatch(
            self::$batchId
        );
        $this->checkError();
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function cancelReceiptBatch()
    {
        $this->initOpenReceiptBatchDepositDate();
        self::$client->closeReceiptBatch(
            self::$batchId
        );
        $this->checkError();
    }
}
