<?php
namespace RentJeeves\DataBundle\Tests\Unit\Entity;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\TestBundle\BaseTestCase;

class UnitCase extends BaseTestCase
{
    /**
     * @return array
     */
    public function providerForGetActualName()
    {
        return [
            [ApiIntegrationType::RESMAN, 'A1', 'A1', false],
            [ApiIntegrationType::MRI, 'A123', 'A123', true],
            [ApiIntegrationType::MRI, 'A123', 'A123', false],
            [ApiIntegrationType::MRI, 'A_123', '123', false],
            [ApiIntegrationType::MRI, 'A_123', 'A123', true],
            [ApiIntegrationType::RESMAN, 'A123', 'A123', false],
        ];
    }

    /**
     * @test
     * @dataProvider providerForGetActualName
     *
     * @param string $apiIntegrationType
     * @param string $unitName
     * @param string $unitNameResult
     * @param boolean $isMultipleBildings
     */
    public function getActualName($apiIntegrationType, $unitName, $unitNameResult, $isMultipleBildings)
    {
        $unit = new Unit();
        $property = new Property();
        $property->setIsMultipleBuildings($isMultipleBildings);
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $unit->setGroup($group);
        $unit->setProperty($property);
        $unit->setName($unitName);
        $unit->getGroup()->getHolding()->setApiIntegrationType($apiIntegrationType);

        $this->assertEquals($unitNameResult, $unit->getName());
    }
}
