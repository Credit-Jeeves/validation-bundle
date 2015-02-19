<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\ResMan;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\ExternalApiBundle\Model\ResMan\Batch;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;
use RentJeeves\ExternalApiBundle\Model\ResMan\Transaction\ResidentTransactions as PaymentTransaction;

class ResManClientCase extends Base
{
    const EXTERNAL_PROPERTY_ID = 'b342e58c-f5ba-4c63-b050-cf44439bb37d';

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
     * @var string
     */
    protected static $propertyId;

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

        self::$customerId = $rtCustomer->getCustomerId();
        self::$userName = $customer->getUserName()->getFirstName().' '.$customer->getUserName()->getLastName();
        self::$unitId = $rtCustomer->getRtUnit()->getUnitId();
        self::$propertyId = $rtCustomer->getRtUnit()->getUnit()->getPropertyPrimaryID();
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

        $batchId = $resManClient->openBatch(self::$propertyId, new \DateTime());
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
            [self::$batchId, self::$customerId, self::$userName, self::$unitId, self::$propertyId],
            $xml
        );

        $container = $this->getKernel()->getContainer();
        $resManClient = $container->get('resman.client');

        $settings = new ResManSettings();
        $settings->setAccountId('400');
        $resManClient->setSettings($settings);
        $result = $resManClient->addPaymentToBatch($residentTransactionXml, self::$propertyId);
        $this->assertTrue($result);
    }

    /**
     * @test
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

    /**
     * @test
     */
    public function shouldCheckSerializeOrderWorksCorrect()
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com',
            )
        );

        $this->assertNotNull($tenant);
        /** @var Order $order */
        $order = $em->getRepository('DataBundle:Order')->findOneBy(
            array(
                'user'  => $tenant->getId(),
                'type'  => OrderType::HEARTLAND_CARD
            )
        );

        $this->assertNotNull($order);
        $order->setBatchId('testBatchId');
        $paymentTransaction = new PaymentTransaction([$order]);
        $context = new SerializationContext();
        $context->setGroups(['ResMan']);
        $context->setSerializeNull(true);
        $result = $this->getContainer()->get('serializer')->serialize($paymentTransaction, 'xml', $context);

        $kernel = $this->getKernel();
        $path = $kernel->locateResource(
            '@ExternalApiBundle/Resources/fixtures/resmanAddPaymentToBatchSerializerCheck.xml'
        );
        $xml = file_get_contents($path);
        $xml = str_replace('%date%', $order->getTransactionDate(), $xml);

        $this->assertEquals(trim($xml), trim($result));
    }
}
