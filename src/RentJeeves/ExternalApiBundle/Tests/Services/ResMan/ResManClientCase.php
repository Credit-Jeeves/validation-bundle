<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\ExternalApiBundle\Model\ResMan\Batch;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResManClientCase extends Base
{
    const EXTERNAL_PROPERTY_ID = 'B342E58C-F5BA-4C63-B050-CF44439BB37D';

    /**
     * @var string
     */
    protected static $batchId;

    /**
     * @var string
     */
    protected static $userName;

    /**
     * @var string
     */
    protected static $unitId;

    /**
     * @var string
     */
    protected static $customerId;

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
        $rtCustomer = $rtCustomers[2];

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

        /** @var Customer $customer */
        $customer = $rtCustomer->getCustomers()->getCustomer()[0];

        self::$customerId = $customer->getCustomerId();
        self::$userName = $customer->getUserName()->getFirstName().' '.$customer->getUserName()->getLastName();
        self::$unitId = $rtCustomer->getRtUnit()->getUnitId();
    }

    /**
     * @test
     * @depends shouldReturnResidentTransactions
     */
    public function shouldOpenNewBatch()
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);

        $batchId= $resManClient->openBatch(self::EXTERNAL_PROPERTY_ID, new \DateTime());
        $this->assertNotEmpty(self::$batchId = $batchId);
    }

    /**
     * @test
     * @depends shouldOpenNewBatch
     */
    public function shouldAddPaymentToBatch()
    {
        $kernel = $this->getKernel();
        $path = $kernel->locateResource(
            '@ExternalApiBundle/Resources/fixtures/resmanAddPaymentToBatch.xml'
        );
        $xml = file_get_contents($path);
        $residentTransactionXml = str_replace(
            ['%batchId%', '%CustomerId%', '%userName%', '%unitName%', '%externalPropertyId%'],
            [self::$batchId, self::$customerId, self::$userName, self::$unitId, self::EXTERNAL_PROPERTY_ID],
            $xml
        );

        $container = $this->getKernel()->getContainer();
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);
        $result = $resManClient->addPaymentToBatch($residentTransactionXml, self::EXTERNAL_PROPERTY_ID);
        $this->assertTrue($result);
    }
}
