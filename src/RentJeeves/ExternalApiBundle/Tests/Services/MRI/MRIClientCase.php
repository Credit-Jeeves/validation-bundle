<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\ExternalApiBundle\Model\MRI\Charge;
use RentJeeves\ExternalApiBundle\Model\MRI\Resident;
use RentJeeves\ExternalApiBundle\Model\MRI\ResidentialRentRoll;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class MRIClientCase extends Base
{
    const PROPERTY_ID = '500';

    const RESIDENT_ID = '0000000001';

    /**
     * @return \CreditJeeves\DataBundle\Entity\Order
     */
    protected function getOrder()
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

        return $order;
    }

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
        $mriClient->setDebug(false);
        $mriResponse = $mriClient->getResidentTransactions(self::PROPERTY_ID);
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\MRIResponse', $mriResponse);
        $this->assertGreaterThan(
            18,
            count($mriResponse->getValues()),
            'MRI Dataset not the size expected - did it change?'
        );

        /** @var Value $value */
        $value = $mriResponse->getValues()[3];
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\Value', $value);
        $this->assertNotEmpty($value->getResidentId());
        $this->assertNotEmpty($value->getUnitId());
        $this->assertNotEmpty($value->getFirstName());
        $this->assertNotEmpty($value->getLastName());
        $this->assertGreaterThan(0, $value->getLeaseBalance());
        $this->assertNotEmpty($value->getLeaseMonthlyRentAmount());
        $this->assertInstanceOf('\DateTime', $value->getLastUpdateDate());
        $this->assertInstanceOf('\DateTime', $value->getLeaseEnd());
    }

    /**
     * @test
     */
    public function shouldPostPayments()
    {
        $mriClient = $this->getMriClient();
        $mriClient->setDebug(false);
        $order = $this->getOrder();
        $property = $order->getContract()->getProperty();
        /** @var PropertyMapping $propertyMapping */
        $propertyMapping = $property->getPropertyMappingByHolding($order->getContract()->getHolding());
        $propertyMapping->setExternalPropertyId(self::PROPERTY_ID);
        $tenant = $order->getContract()->getTenant();
        /** @var ResidentMapping $tenantMapping */
        $residentMapping = $tenant->getResidentForHolding($order->getContract()->getHolding());
        $residentMapping->setResidentId(self::RESIDENT_ID);

        $transaction = $order->getCompleteTransaction();
        $transaction->setTransactionId(uniqid());
        $transaction->setCreatedAt(new \DateTime());
        $order->setSum(rand(100, 100000));
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($residentMapping);
        $em->persist($transaction);
        $em->persist($propertyMapping);
        $em->flush();

        $this->assertTrue($mriClient->postPayment($order, self::PROPERTY_ID));
    }

    /**
     * @test
     */
    public function shouldReturnResidentialRentRoll()
    {
        $mriClient = $this->getMriClient();
        $mriClient->setDebug(false);
        /** @var ResidentialRentRoll $mriResponse */
        $mriResponse = $mriClient->getResidentialRentRoll(self::PROPERTY_ID);
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\ResidentialRentRoll', $mriResponse);
        $this->assertNotEmpty($values = $mriResponse->getValues(), 'ResidentialRentRoll should have entry');
        /** @var Value $value */
        $value = reset($values);
        $this->assertNotEmpty($currentCharges = $value->getCurrentCharges(), 'Entry should have Charges');
        $this->assertNotEmpty($charges = $currentCharges->getCharges(), 'CurrentCharges should have charges');
        /** @var Charge $charge */
        $charge = reset($charges);
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\Charge', $charge);
        $this->assertNotEmptyWithMessage($charge->getAmount(), 'Amount for charge');
        $this->assertNotEmptyWithMessage($charge->getBuildingId(), 'Building for charge');
        $this->assertNotEmptyWithMessage($charge->getUnitId(), 'UnitId for charge');
        $this->assertNotEmptyWithMessage($charge->getChargeCode(), 'ChargeCode for charge');
        $this->assertNotEmptyWithMessage($charge->getEffectiveDate(), 'EffectiveDate for charge');
        $this->assertNotEmptyWithMessage($charge->getPropertyId(), 'PropertyId for charge');
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Model\MRI\Residents',
            $residents = $value->getResidents()
        );
        $residentsArray = $residents->getResidentArray();
        /** @var Resident $resident */
        $resident = reset($residentsArray);
        $this->assertNotEmptyWithMessage($resident->getResidentId(), 'ResidentId for resident');
        $this->assertNotEmptyWithMessage($resident->getResidentStatus(), 'Status for resident');
        $this->assertNotEmptyWithMessage(
            $nextPageLink = $mriResponse->getNextPageLink(),
            'Next page link for ResidentRentRoll'
        );

        $mriResponse = $mriClient->getResidentialRentRollByNextPageLink($nextPageLink);
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Model\MRI\ResidentialRentRoll', $mriResponse);
    }
}
