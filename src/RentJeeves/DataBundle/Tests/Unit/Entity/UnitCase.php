<?php
namespace RentJeeves\DataBundle\Tests\Unit\Entity;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\TestBundle\BaseTestCase;

class UnitCase extends BaseTestCase
{
    /**
     * @return array
     */
    public function providerForGetActualName()
    {
        return [
            [AccountingSystem::YARDI_VOYAGER, 'A1', 'A1', 'U1|23|99', false],
            [AccountingSystem::MRI, 'A123', '23A123', 'U1|23|99', true],
            [AccountingSystem::MRI, 'A123', 'A123', 'U1|23|99', false],
            [AccountingSystem::MRI, 'A_123', 'A_123', 'U1|23|99', false],
            [AccountingSystem::MRI, 'A_123', '23A_123', 'U1|23|99', true],
            [AccountingSystem::YARDI_VOYAGER, 'A123', 'A123', 'U1|23|99', false],
            [AccountingSystem::RESMAN, 'A123', '23A123', 'U1|23|99', true],
            [AccountingSystem::RESMAN, 'A123', 'A123', 'U1|23|99', false],
            [AccountingSystem::RESMAN, 'A_123', 'A_123', 'U1|23|99', false],
            [AccountingSystem::RESMAN, 'A_123', '23A_123', 'U1|23|99', true],
        ];
    }

    /**
     * @test
     * @dataProvider providerForGetActualName
     *
     * @param string $accountingSystem
     * @param string $unitName
     * @param string $unitNameResult
     * @param string $externalUnitId
     * @param boolean $isMultipleBildings
     */
    public function getActualName($accountingSystem, $unitName, $unitNameResult, $externalUnitId, $isMultipleBildings)
    {
        $unit = new Unit();
        $property = new Property();
        $propertyAddress = new PropertyAddress();
        $property->setPropertyAddress($propertyAddress);
        $property->setIsMultipleBuildings($isMultipleBildings);
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $unit->setGroup($group);
        $unit->setProperty($property);
        $unit->setName($unitName);
        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId($externalUnitId);
        $unit->setUnitMapping($unitMapping);

        $unit->getGroup()->getHolding()->setAccountingSystem($accountingSystem);

        $this->assertEquals($unitNameResult, $unit->getName());
    }
}
