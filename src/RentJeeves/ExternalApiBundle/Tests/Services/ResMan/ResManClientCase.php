<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\ExternalApiBundle\Model\ResMan\Batch;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResManClientCase extends Base
{
    const EXTERNAL_PROPERTY_ID = 'B342E58C-F5BA-4C63-B050-CF44439BB37D';

    /**
     * @test
     */
    public function shouldReturnResidentTransactions()
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);

        /** @var $residentTransactions ResidentTransactions */
        $residentTransactions = $resManClient->getResidentTransactions(self::EXTERNAL_PROPERTY_ID);

        $rtCustomers = $residentTransactions->getProperty()->getRtCustomers();
        $this->assertInternalType('array', $rtCustomers);
        $this->assertGreaterThan(1, count($rtCustomers));
        /** @var $rtCustomer RtCustomer */
        $rtCustomer = reset($rtCustomers);
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\ResMan\Customers',
            $rtCustomer->getCustomers()
        );
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit',
            $rtCustomer->getRtUnit()
        );
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\ResMan\RtServiceTransactions',
            $rtCustomer->getRtServiceTransactions()
        );
    }

    /**
     * @test
     */
    public function shouldOpenNewBatch()
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);

        /** @var Batch $openBatch */
        $openBatch = $resManClient->sendOpenBatch(self::EXTERNAL_PROPERTY_ID, new \DateTime());

        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\ResMan\Batch',
            $openBatch
        );

        $this->assertNotEmpty($openBatch->getBatchId());
    }
}
