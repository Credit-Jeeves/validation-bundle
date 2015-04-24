<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class ResManClientCase extends Base
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    const EXTERNAL_PROPERTY_ID = 'b342e58c-f5ba-4c63-b050-cf44439bb37d';

    const RESIDENT_ID = '09948a58-7c50-4089-8942-77e1456f40ec';

    const EXTERNAL_LEASE_ID = '09948a58-7c50-4089-8942-77e1456f40ec';

    /**
     * @var string
     */
    public static $batchId;

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
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions',
            $residentTransactions
        );

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
    }

    /**
     * #test
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

        $batchId = $resManClient->openBatch(self::EXTERNAL_PROPERTY_ID, new \DateTime());
        $this->assertNotEmpty(self::$batchId = $batchId);
    }

    /**
     * #test
     * @depends shouldOpenNewBatch
     */
    public function shouldAddPaymentToBatch()
    {
        $this->load(true);
        $container = $this->getKernel()->getContainer();
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);
        $transaction = $this->createTransaction(
            ApiIntegrationType::RESMAN,
            self::RESIDENT_ID,
            self::EXTERNAL_PROPERTY_ID,
            self::EXTERNAL_LEASE_ID
        );

        $order = $transaction->getOrder();
        $this->assertNotNull($order);

        $result = $resManClient->addPaymentToBatch($order, self::EXTERNAL_PROPERTY_ID);
        $this->assertTrue($result);
    }

    /**
     * #test
     * @depends shouldOpenNewBatch
     */
    public function shouldCloseBatch()
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);

        $result = $resManClient->closeBatch(self::EXTERNAL_PROPERTY_ID, self::$batchId);

        $this->assertTrue($result);
    }
}
