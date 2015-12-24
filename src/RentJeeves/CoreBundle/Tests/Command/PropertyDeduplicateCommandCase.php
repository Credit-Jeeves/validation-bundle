<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\PropertyDeduplicateCommand;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class PropertyDeduplicateCommandCase extends BaseTestCase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage propertyAddress with id = 0 not found.
     */
    public function shouldThrowExceptionIfSendNotCorrectPropertyId()
    {
        $this->executeCommandTester(new PropertyDeduplicateCommand(), ['--property-address-id' => 0]);
    }

    /**
     * @test
     */
    public function shouldDeduplicateAllDubbedPropertyIfAllPropertyAreValidForDedupe()
    {
        $this->load(true);

        $dstProperty = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(1);
        $srcProperty1 = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(2);
        $srcProperty2 = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(3);
        $propertyAddress = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->find(1);
        $propertyAddress->setIsSingle(false);

        $this->assertNotEquals(
            $propertyAddress,
            $srcProperty1->getPropertyAddress(),
            'srcProperty1 should have not PropertyAddress#1'
        );
        $this->assertNotEquals(
            $propertyAddress,
            $srcProperty2->getPropertyAddress(),
            'srcProperty2 should have not PropertyAddress#1'
        );
        // set identity ExternalPropertyId
        $srcProperty1->setPropertyAddress($propertyAddress);
        $srcProperty2->setPropertyAddress($propertyAddress);
        $dstExternalPropertyId = $dstProperty->getPropertyMappings()->first()->getExternalPropertyId();
        $srcProperty1->getPropertyMappings()->first()->setExternalPropertyId($dstExternalPropertyId);

        $this->writeAttribute($srcProperty1, 'property_groups', $dstProperty->getPropertyGroups());
        $this->writeAttribute($srcProperty2, 'property_groups', $dstProperty->getPropertyGroups());

        $this->getEntityManager()->flush();

        $this->executeCommandTester(new PropertyDeduplicateCommand(), ['--property-address-id' => 1]);

        $this->assertNull(
            $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(2),
            'Dubbed Property#2 should be deleted.'
        );
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')->findBy(['property' => 2]),
            'PropertyMappings for Dubbed Property#2 should be deleted.'
        );
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findBy(['property' => 2]),
            'Units for Dubbed Property#2 should be deleted.'
        );
        $this->assertNull(
            $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(3),
            'Dubbed Property#3 should be deleted.'
        );
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findBy(['property' => 3]),
            'Units for Dubbed Property#3 should be deleted.'
        );
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')->findBy(['property' => 3]),
            'PropertyMappings for Dubbed Property#3 should be deleted.'
        );
    }

    /**
     * @test
     */
    public function shouldDeduplicateOnlySecondPropertyIfFirstPropertyIsNotValidForDedupe()
    {
        $this->load(true);

        $dstProperty = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(1);
        $srcProperty1 = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(2);
        $srcProperty2 = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(3);
        $propertyAddress = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->find(1);

        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneBy([]);
        /** @var Unit $srcUnit */
        $srcUnit = $srcProperty1->getUnits()->first();
        $contract = new Contract();
        $contract->setUnit($srcUnit);
        $contract->setGroup($srcUnit->getGroup());
        $contract->setProperty($srcProperty1);
        $contract->setTenant($tenant);
        $contract->setHolding($srcUnit->getHolding());
        $contract->setStatus(ContractStatus::APPROVED);
        $this->getEntityManager()->persist($contract);

        $srcUnit->getGroup()->getGroupSettings()->setExternalResidentFollowsUnit(true);

        $this->assertNotEquals(
            $propertyAddress,
            $srcProperty1->getPropertyAddress(),
            'srcProperty1 should have not PropertyAddress#1'
        );
        $this->assertNotEquals(
            $propertyAddress,
            $srcProperty2->getPropertyAddress(),
            'srcProperty2 should have not PropertyAddress#1'
        );
        // set identity ExternalPropertyId
        $srcProperty1->setPropertyAddress($propertyAddress);
        $srcProperty2->setPropertyAddress($propertyAddress);
        $dstExternalPropertyId = $dstProperty->getPropertyMappings()->first()->getExternalPropertyId();
        $srcProperty1->getPropertyMappings()->first()->setExternalPropertyId($dstExternalPropertyId);

        $this->writeAttribute($srcProperty1, 'property_groups', $dstProperty->getPropertyGroups());
        $this->writeAttribute($srcProperty2, 'property_groups', $dstProperty->getPropertyGroups());

        $this->getEntityManager()->flush();

        $this->executeCommandTester(new PropertyDeduplicateCommand(), ['--property-address-id' => 1]);

        $this->assertNotNull(
            $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(2),
            'Dubbed Property#2 should not be deleted.'
        );
        $this->assertNotEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')->findBy(['property' => 2]),
            'PropertyMappings for Dubbed Property#2 should be deleted.'
        );
        $this->assertNotEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findBy(['property' => 2]),
            'Units for Dubbed Property#2 should be deleted.'
        );
        $this->assertNull(
            $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(3),
            'Dubbed Property#3 should be deleted.'
        );
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findBy(['property' => 3]),
            'Units for Dubbed Property#3 should be deleted.'
        );
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')->findBy(['property' => 3]),
            'PropertyMappings for Dubbed Property#3 should be deleted.'
        );
    }

    /**
     * @test
     */
    public function shouldDeduplicatePropertyAndRemoveUnitIfPropertyIsSingle()
    {
        $this->load(true);

        $propertyAddress = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->find(19);
        $dstProperty = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find(19);

        $srcProperty = new Property();
        $srcProperty->setGroups($dstProperty->getPropertyGroups());
        $srcProperty->setPropertyAddress($propertyAddress);
        $this->getContainer()->get('property.manager')->setupSingleProperty($srcProperty);
        $this->getEntityManager()->flush();

        $srcUnitId = $srcProperty->getUnits()->first()->getId();
        $srcPropertyId = $srcProperty->getId();

        $this->executeCommandTester(new PropertyDeduplicateCommand(), ['--property-address-id' => 19]);

        $this->assertNull(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find($srcUnitId),
            'isSingle Unit should be deleted.'
        );
        $this->assertNull(
            $this->getEntityManager()->getRepository('RjDataBundle:Property')->find($srcPropertyId),
            'isSingle Property should be deleted.'
        );
    }
}
