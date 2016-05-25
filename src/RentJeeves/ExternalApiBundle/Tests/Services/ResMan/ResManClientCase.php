<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;
use RentJeeves\TestBundle\Services\ResMan\ResManClient as TestResManClient;

class ResManClientCase extends Base
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    const EXTERNAL_PROPERTY_ID = '4fb18691-c894-4b84-805b-c62da481ca63';

    const RESIDENT_ID = '59754031-53e8-4f1c-9b95-ba4db3d40ba2';

    const EXTERNAL_LEASE_ID = 'c99570ab-aaa0-4b5c-b956-f70d791269e9';

    const EXTERNAL_UNIT_ID = '4fb18691-c894-4b84-805b-c62da481ca63|1|12001';

    const RESMAN_UNIT_ID = '12001';

    /**
     * @test
     */
    public function shouldReturnResidentTransactions()
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient ResManClient */
        $resManClient = $container->get('resman.client');
        $settings = $this->getEntityManager()->getRepository('RjDataBundle:ResManSettings')->findOneBy([]);
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
        $settings = $this->getEntityManager()->getRepository('RjDataBundle:ResManSettings')->findOneBy([]);
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

        $settings = $this->getEntityManager()->getRepository('RjDataBundle:ResManSettings')->findOneBy([]);
        $resManClient->setSettings($settings);
        $transaction = $this->createTransaction(
            AccountingSystem::RESMAN,
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
     * @param $batchId
     *
     * @test
     * @depends shouldOpenNewBatch
     *
     * @return string
     */
    public function shouldAddDepositToBatch($batchId)
    {
        $container = $this->getKernel()->getContainer();
        /** @var ResManClient $resManClient */
        $resManClient = $container->get('resman.client');

        $settings = $this->getEntityManager()->getRepository('RjDataBundle:ResManSettings')->findOneBy([]);
        $resManClient->setSettings($settings);
        $transaction = $this->createTransaction(
            AccountingSystem::RESMAN,
            self::RESIDENT_ID,
            self::EXTERNAL_PROPERTY_ID,
            self::EXTERNAL_LEASE_ID,
            self::EXTERNAL_UNIT_ID
        );

        $order = $transaction->getOrder();
        $this->assertNotNull($order);
        $order->setBatchId($batchId);
        $depositAccount = $order->getDepositAccount();
        $this->assertNotNull($depositAccount, 'DepositAccount should be set for Order');
        $depositAccount->setType(DepositAccountType::SECURITY_DEPOSIT);
        $order->setDepositAccount($depositAccount);
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
                'user' => $tenant->getId(),
                'paymentType' => OrderPaymentType::CARD,
            ]
        );
        /** @var UnitMapping $unitMapping */
        $unitMapping = $em->getRepository('RjDataBundle:UnitMapping')->findOneByExternalUnitId('AAABBB-7');
        $this->assertNotEmpty($unitMapping, 'Should have unitMapping with externalUnitId AAABBB-7 in DB.');
        $unitMapping->setExternalUnitId(self::EXTERNAL_UNIT_ID);
        $em->flush();
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

        $settings = $this->getEntityManager()->getRepository('RjDataBundle:ResManSettings')->findOneBy([]);
        $resManClient->setSettings($settings);

        $result = $resManClient->closeBatch($batchId, self::EXTERNAL_PROPERTY_ID);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function shouldGetResidentTransactionsWithEmptyMoveOutDate()
    {
        $container = $this->getKernel()->getContainer();
        /** @var $resManClient TestResManClient */
        $resManClient = $container->get('resman.client');
        $resManClient->setResponseMockFileWithEmptyMoveOut();
        $resMan = $resManClient->getResidentTransactions(TestResManClient::TEST_EXTERNAL_PROPERTY_ID);

        /** @var Customer $customer */
        $customer = $resMan->getProperty()->getRtCustomers()[0]->getCustomers()->getCustomer()->first();

        $this->assertNotEmpty($customer, 'Expected: Customer should be in collection');
        $this->assertNotEmpty($lease = $customer->getLease(), 'Expected: Customer should be in collection');
        $this->assertEmpty($lease->getActualMoveOut(), 'Expected: Move out date should be empty string');
        $this->assertNull(
            $lease->getActualMoveOutDateObject(),
            'Function getActualMoveOutDateObject should be return null when moveOut is empty string'
        );
    }
}
