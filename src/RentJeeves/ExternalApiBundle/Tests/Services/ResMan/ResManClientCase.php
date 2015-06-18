<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Entity\Tenant;
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

    const EXTERNAL_UNIT_ID = '2';

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
     * @test
     * @depends shouldReturnResidentTransactions
     *
     * @return string
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
        $this->assertNotEmpty($batchId);

        return $batchId;
    }

    /**
     * @param $batchId
     *
     * @test
     * @depends shouldOpenNewBatch
     *
     * @return string
     */
    public function shouldAddPaymentToBatch($batchId)
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
            self::EXTERNAL_LEASE_ID,
            self::EXTERNAL_UNIT_ID
        );

        $order = $transaction->getOrder();
        $this->assertNotNull($order);
        $order->setBatchId($batchId);
        $result = $resManClient->addPaymentToBatch($order, self::EXTERNAL_PROPERTY_ID);
        $this->assertTrue($result);

        return $batchId;
    }

    /**
     *
     * We typically don't want to test protected methods, but this serialization depends on many
     * external classes which makes it fragile -- so it is probably warranted in this case.
     *
     * @param $batchId
     *
     * @test
     * @depends shouldOpenNewBatch
     *
     * @return string
     */
    public function shouldCheckSerializeOrderWorksCorrect($batchId)
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            ['email' => 'tenant11@example.com']
        );

        $this->assertNotNull($tenant);
        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(
            [
                'user'  => $tenant->getId(),
                'type'  => OrderType::HEARTLAND_CARD
            ]
        );

        $this->assertNotNull($order);
        $order->setBatchId('testBatchId');

        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');
        // using reflection to enable us to test a protected method
        $r = new \ReflectionMethod(
            'RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient',
            'getResidentTransactionXml'
        );
        $r->setAccessible(true);
        $result = $r->invoke($resManClient, $order);

        $kernel = $this->getKernel();
        $path = $kernel->locateResource(
            '@ExternalApiBundle/Resources/fixtures/resmanAddPaymentToBatchSerializerCheck.xml'
        );
        $xml = file_get_contents($path);
        $xml = str_replace('%date%', $order->getTransactionDate(), $xml);

        $this->assertEquals(trim($xml), trim($result));
    }

    /**
     * @param $batchId
     *
     * @test
     * @depends shouldAddPaymentToBatch
     */
    public function shouldCloseBatch($batchId)
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);

        $result = $resManClient->closeBatch($batchId, self::EXTERNAL_PROPERTY_ID);

        $this->assertTrue($result);
    }
}
