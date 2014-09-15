<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit;

use RentJeeves\CoreBundle\DateTime;
use RentJeeves\ExternalApiBundle\Soap\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Tests\Unit\BaseClientCase as Base;

class PaymentClientCase extends Base
{
    protected static $batchId;

    protected static $client;

    /**
     * @test
     */
    public function openReceiptBatchDepositDate()
    {
        $container = $this->getKernel()->getContainer();
        $clientFactory = $container->get('soap.client.factory');

        self::$client = $clientFactory->getClient(
            $this->getYardiSettings(),
            SoapClientEnum::PAYMENT
        );

        self::$batchId = self::$client->openReceiptBatchDepositDate(
            new DateTime(),
            $yardiPropertyId = 'rnttrk01',
            $description = 'Test open date'
        );

        if (self::$client->isError()) {
            $this->assertFalse(true, self::$client->getErrorMessage());
        }
    }

    /**
     * @test
     * @depends openReceiptBatchDepositDate
     */
    public function closeReceiptBatch()
    {
        $result = self::$client->closeReceiptBatch(
            self::$batchId
        );

        if (self::$client->isError()) {
            $this->assertFalse(true, self::$client->getErrorMessage());
        }

        $this->assertTrue($result);
    }
}
