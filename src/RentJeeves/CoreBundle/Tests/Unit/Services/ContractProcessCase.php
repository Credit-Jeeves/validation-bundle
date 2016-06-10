<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class ContractProcessCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldSuccessCreateContractJustForPropertyWithOneSubmerchantGroup()
    {
        $entityManagerMock = $this->getEntityManagerMock();
        $entityManagerMock->expects($this->once())->method('persist');
        $entityManagerMock->expects($this->once())->method('flush');

        $holding = new Holding();

        $submerchantGroup = new Group();
        $submerchantGroup->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $submerchantGroup->setHolding($holding);
        $this->writeIdAttribute($submerchantGroup, 1);

        $dtrGroup = new Group();
        $dtrGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $dtrGroup->setHolding($holding);
        $this->writeIdAttribute($dtrGroup, 2);

        $tenant = new Tenant();

        $unit = new Unit();
        $unit->setName('testName');
        $unit->setGroup($submerchantGroup);
        $this->writeIdAttribute($unit, 2);

        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIsSingle(false);

        $property = new Property();
        $property->addPropertyGroup($submerchantGroup);
        $property->addPropertyGroup($dtrGroup);
        $property->setPropertyAddress($propertyAddress);

        $contractProcess = new ContractProcess($entityManagerMock, $this->getValidatorMock());

        $contract = $contractProcess->createContractForOneSubmerchantGroup($tenant, $property, 'testName');

        $this->assertInstanceOf('RentJeeves\DataBundle\Entity\Contract', $contract, 'Contract should be created');

        $this->assertEquals(
            $submerchantGroup->getId(),
            $contract->getGroup()->getId(),
            'Contract should be created for submerchant group'
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Property #1 does't have submerchant group
     */
    public function shouldThrowExceptionIfPropertyHasNoSubmerchantGroupWhenCreatingSubmerchantContract()
    {
        $holding = new Holding();

        $submerchantGroup = new Group();
        $submerchantGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $submerchantGroup->setHolding($holding);
        $this->writeIdAttribute($submerchantGroup, 1);

        $dtrGroup = new Group();
        $dtrGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $dtrGroup->setHolding($holding);
        $this->writeIdAttribute($dtrGroup, 2);

        $tenant = new Tenant();

        $propertyAddress = new PropertyAddress();

        $property = new Property();
        $property->addPropertyGroup($submerchantGroup);
        $property->addPropertyGroup($dtrGroup);
        $property->setPropertyAddress($propertyAddress);
        $this->writeIdAttribute($property, 1);

        $contractProcess = new ContractProcess($this->getEntityManagerMock(), $this->getValidatorMock());

        $contractProcess->createContractForOneSubmerchantGroup($tenant, $property);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Property #1 has more then one submerchant groups
     */
    public function shouldThrowExceptionIfPropertyHasMoreOneSubmerchantGroupWhenCreatingSubmerchantContract()
    {
        $holding = new Holding();

        $submerchantGroup = new Group();
        $submerchantGroup->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $submerchantGroup->setHolding($holding);
        $this->writeIdAttribute($submerchantGroup, 1);

        $dtrGroup = new Group();
        $dtrGroup->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $dtrGroup->setHolding($holding);
        $this->writeIdAttribute($dtrGroup, 2);

        $tenant = new Tenant();

        $propertyAddress = new PropertyAddress();

        $property = new Property();
        $property->addPropertyGroup($submerchantGroup);
        $property->addPropertyGroup($dtrGroup);
        $property->setPropertyAddress($propertyAddress);
        $this->writeIdAttribute($property, 1);

        $contractProcess = new ContractProcess($this->getEntityManagerMock(), $this->getValidatorMock());

        $contractProcess->createContractForOneSubmerchantGroup($tenant, $property);
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Unit #2 exists but belongs to another group then property #1.
     */
    public function shouldThrowExceptionIfUnitBelongsAnotherGroupWhenCreatingSubmerchantContract()
    {
        $holding = new Holding();

        $submerchantGroup = new Group();
        $submerchantGroup->setOrderAlgorithm(OrderAlgorithmType::SUBMERCHANT);
        $submerchantGroup->setHolding($holding);
        $this->writeIdAttribute($submerchantGroup, 1);

        $dtrGroup = new Group();
        $dtrGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $dtrGroup->setHolding($holding);
        $this->writeIdAttribute($dtrGroup, 2);

        $unit = new Unit();
        $unit->setName('testName');
        $unit->setGroup($dtrGroup);
        $this->writeIdAttribute($unit, 2);

        $tenant = new Tenant();

        $propertyAddress = new PropertyAddress();
        $propertyAddress->setIsSingle(false);

        $property = new Property();
        $property->addPropertyGroup($submerchantGroup);
        $property->addPropertyGroup($dtrGroup);
        $property->setPropertyAddress($propertyAddress);
        $property->addUnit($unit);
        $this->writeIdAttribute($property, 1);

        $contractProcess = new ContractProcess($this->getEntityManagerMock(), $this->getValidatorMock());

        $contractProcess->createContractForOneSubmerchantGroup($tenant, $property, 'testName');
    }
}
