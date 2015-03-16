<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\ExternalApiBundle\Model\MRI\Payment;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class MRIClientCase extends Base
{
    const PROPERTY_ID = '500';

    /**
     * @return MRIClient
     */
    protected function getMriClient()
    {
        $container = $this->getKernel()->getContainer();
        /** @var MRIClient $mriClient */
        $mriClient = $container->get('mri.client');
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\MRI\MRIClient',
            $mriClient
        );
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Holding $holding */
        $holding = $em->getRepository('DataBundle:Holding')->findOneBy(
            array(
                'name' => 'Rent Holding',
            )
        );

        $this->assertNotNull($holding);
        $mriSettings = $holding->getMriSettings();
        $this->assertNotNull($mriSettings);
        $mriClient->setSettings($mriSettings);
        $mriClient->setDebug(false);

        return $mriClient;
    }

    /**
     * @test
     */
    public function shouldReturnResidents()
    {
        $mriClient = $this->getMriClient();
        $mriResponse = $mriClient->getResidentTransactions(self::PROPERTY_ID);
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse', $mriResponse);
        $this->assertGreaterThan(15, $mriResponse->getValues());
        /** @var Value $value */
        $value = $mriResponse->getValues()[14];
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\Value', $value);
        $this->assertNotEmpty($value->getResidentId());
        $this->assertNotEmpty($value->getUnitId());
        $this->assertNotEmpty($value->getFirstName());
        $this->assertNotEmpty($value->getLastName());
        $this->assertNotEmpty($value->getLeaseBalance());
        $this->assertNotEmpty($value->getLeaseMonthlyRentAmount());
        $this->assertInstanceOf('\DateTime', $value->getLastUpdateDate());
        $this->assertInstanceOf('\DateTime', $value->getLeaseMoveOut());
        $this->assertInstanceOf('\DateTime', $value->getLeaseEnd());
        $this->assertInstanceOf('\DateTime', $value->getLeaseStart());
    }

    /**
     * @TODO currently API not return any data, when it will return some data need refactoring test
     * @test
     */
    public function shouldReturnPaymentDetails()
    {
        $mriClient = $this->getMriClient();
        $mriResponse = $mriClient->getPaymentDetails(self::PROPERTY_ID);
        $this->assertNotEmpty($mriResponse->getMetadata());
    }

    /**
     * @test
     */
    public function shouldCheckPaymentXml()
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com',
            )
        );
        $this->assertNotEmpty($tenant);
        /** @var Property $property */
        $property = $em->getRepository('RjDataBundle:Property')->findOneBy(
            array(
                'street' => 'Broadway',
                'number' => '770',
                'zip'    => '10003'
            )
        );
        $this->assertNotEmpty($property);
        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy([
            'property'   => $property,
            'tenant'     => $tenant,
            'rent'       => '1750.00'
        ]);

        $this->assertNotEmpty($contract);
        $operations = $contract->getOperations();
        $this->assertNotEmpty($operations);
        /** @var Operation $operation */
        $operation = $operations->first();
        $this->assertNotEmpty($operation);
        $this->assertNotEmpty($order = $operation->getOrder());
        $this->assertNotEmpty($transaction = $order->getCompleteTransaction());

        /** @var MRIClient $mriClient */
        $mriClient = $this->getMriClient();
        $payment = new Payment();
        $payment->setEntry($order);

        $xml = $mriClient->getPaymentXml($payment);

        $kernel = $this->getKernel();
        $path = $kernel->locateResource(
            '@ExternalApiBundle/Resources/fixtures/mri_payment.xml'
        );

        $fixtureXml = file_get_contents($path);
        $fixtureXml = str_replace(
            ['%date%'],
            [$order->getMriPaymentInitiationDatetime()],
            $xml
        );

        $this->assertEquals($fixtureXml, $xml);
    }
}
