<?php
namespace RentJeeves\CoreBundle\Tests\Command;

use RentJeeves\CoreBundle\Command\UnitDeduplicateCommand;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\TestBundle\Command\BaseTestCase;

class UnitDeduplicateCommandCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unit with id = 0 not found.
     */
    public function shouldThrowExceptionIfSendNotCorrectUnitId()
    {
        $this->executeCommandTester(new UnitDeduplicateCommand(), ['--src-unit-id' => 0]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Property with id = 0 not found.
     */
    public function shouldThrowExceptionIfSendNotCorrectPropertyId()
    {
        $this->load(true);
        $this->executeCommandTester(new UnitDeduplicateCommand(), ['--src-unit-id' => 1, '--dst-property-id' => 0]);
    }

    /**
     * @test
     */
    public function shouldDeduplicateAndMoveAllEntitiesToNewUnitIfPropertyDoesNotHaveUnitWithSameName()
    {
        $this->load(true);
        $unit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1);
        $lastUnit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findOneBy([], ['id' => 'desc']);
        $contracts = $unit->getContracts();

        $this->assertGreaterThan(1, count($contracts));
        /**
         * @var Contract $firstContract
         */
        $firstContract = $contracts->first();
        /**
         * @var Contract $lastContract
         */
        $lastContract = $contracts->last();
        /**
         * @var ContractWaiting $contractWaiting
         */
        $contractWaiting = $unit->getContractsWaiting()->first();
        $this->executeCommandTester(new UnitDeduplicateCommand(), ['--src-unit-id' => 1, '--dst-property-id' => 18]);
        $this->getEntityManager()->clear();
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1),
            'srcUnit is not deleted.'
        );
        $newLastUnit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findOneBy([], ['id' => 'desc']);
        $this->assertNotEquals($lastUnit, $newLastUnit, 'New Unit is not created.');
        $this->assertEquals($unit->getName(), $newLastUnit->getName(), 'New Unit has incorrect name');

        $this->assertEquals(
            $newLastUnit->getId(),
            $firstContract->getUnit()->getId(),
            'firstContract has incorrect Unit'
        );
        $this->assertEquals(
            $newLastUnit->getProperty()->getId(),
            $firstContract->getProperty()->getId(),
            'firstContract has incorrect Property'
        );
        $this->assertEquals(
            $newLastUnit->getId(),
            $lastContract->getUnit()->getId(),
            'lastContract has incorrect Unit'
        );
        $this->assertEquals(
            $newLastUnit->getProperty()->getId(),
            $lastContract->getProperty()->getId(),
            'lastContract has incorrect Property'
        );

        $this->assertEquals(
            $newLastUnit->getId(),
            $contractWaiting->getUnit()->getId(),
            'contractWaiting has incorrect Unit'
        );

        $this->assertEquals(
            $newLastUnit->getProperty()->getId(),
            $contractWaiting->getProperty()->getId(),
            'contractWaiting has incorrect Property'
        );
    }

    /**
     * @test
     */
    public function shouldDeduplicateAndMoveAllEntitiesToExistUnitIfPropertyHaveUnitWithSameName()
    {
        $this->load(true);

        $unit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1);
        $unitWithSameName = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(2);
        $unit->setName($unitWithSameName->getName());

        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId('test');
        $this->getEntityManager()->persist($unitMapping);

        $unit->setUnitMapping($unitMapping);
        $this->getEntityManager()->flush();

        $lastUnit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findOneBy([], ['id' => 'desc']);
        $contracts = $unit->getContracts();

        $this->assertGreaterThan(1, count($contracts));
        /**
         * @var Contract $firstContract
         */
        $firstContract = $contracts->first();
        /**
         * @var Contract $lastContract
         */
        $lastContract = $contracts->last();
        /**
         * @var ContractWaiting $contractWaiting
         */
        $contractWaiting = $unit->getContractsWaiting()->first();

        $this->executeCommandTester(new UnitDeduplicateCommand(), ['--src-unit-id' => 1, '--dst-property-id' => 1]);
        $this->getEntityManager()->clear();
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1),
            'srcUnit is not deleted.'
        );
        $unitMapping = $this->getEntityManager()->getRepository('RjDataBundle:UnitMapping')->findOneBy(
            [
                'externalUnitId' => 'test'
            ]
        );
        $this->assertNotEmpty($unitMapping);
        $this->assertEquals(
            $unitWithSameName->getId(),
            $unitMapping->getUnit()->getId(),
            'UnitMapping is not updated.'
        );

        $newLastUnit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findOneBy([], ['id' => 'desc']);
        $this->assertEquals($lastUnit->getId(), $newLastUnit->getid(), 'New Unit is created.');

        $this->assertEquals(
            $unitWithSameName->getId(),
            $firstContract->getUnit()->getId(),
            'firstContract has incorrect Unit'
        );
        $this->assertEquals(
            $unitWithSameName->getProperty()->getId(),
            $firstContract->getProperty()->getId(),
            'firstContract has incorrect Property'
        );
        $this->assertEquals(
            $unitWithSameName->getId(),
            $lastContract->getUnit()->getId(),
            'lastContract has incorrect Unit'
        );
        $this->assertEquals(
            $unitWithSameName->getProperty()->getId(),
            $lastContract->getProperty()->getId(),
            'lastContract has incorrect Property'
        );

        $this->assertEquals(
            $unitWithSameName->getId(),
            $contractWaiting->getUnit()->getId(),
            'contractWaiting has incorrect Unit'
        );
        $this->assertEquals(
            $unitWithSameName->getProperty()->getId(),
            $contractWaiting->getProperty()->getId(),
            'contractWaiting has incorrect Property'
        );
    }

    /**
     * @test
     */
    public function shouldDeduplicateAndMoveAllEntitiesToExistUnitAndRemoveUnitMappingIfPropertyHaveUnitWithSameName()
    {
        $this->load(true);

        $unit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1);
        $unitWithSameName = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(2);
        $unit->setName($unitWithSameName->getName());

        $unitMapping = new UnitMapping();
        $unitMapping->setUnit($unit);
        $unitMapping->setExternalUnitId('test');
        $this->getEntityManager()->persist($unitMapping);

        $unit->setUnitMapping($unitMapping);

        $unitMapping2 = new UnitMapping();
        $unitMapping2->setUnit($unitWithSameName);
        $unitMapping2->setExternalUnitId('test');
        $this->getEntityManager()->persist($unitMapping2);

        $unitWithSameName->setUnitMapping($unitMapping2);
        $this->getEntityManager()->flush();

        $unitMappingId = $unitMapping->getId();

        $lastUnit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findOneBy([], ['id' => 'desc']);
        $contracts = $unit->getContracts();

        $this->assertGreaterThan(1, count($contracts));
        /**
         * @var Contract $firstContract
         */
        $firstContract = $contracts->first();
        /**
         * @var Contract $lastContract
         */
        $lastContract = $contracts->last();
        /**
         * @var ContractWaiting $contractWaiting
         */
        $contractWaiting = $unit->getContractsWaiting()->first();
        $this->executeCommandTester(new UnitDeduplicateCommand(), ['--src-unit-id' => 1, '--dst-property-id' => 1]);

        $this->getEntityManager()->clear();
        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1),
            'srcUnit is not deleted.'
        );

        $this->assertEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:UnitMapping')->find($unitMappingId),
            'srcUnitMapping is not deleted.'
        );

        $newLastUnit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findOneBy([], ['id' => 'desc']);
        $this->assertEquals($lastUnit->getId(), $newLastUnit->getId(), 'New Unit is created.');

        $this->assertEquals(
            $unitWithSameName->getId(),
            $firstContract->getUnit()->getId(),
            'firstContract has incorrect Unit'
        );
        $this->assertEquals(
            $unitWithSameName->getProperty()->getId(),
            $firstContract->getProperty()->getId(),
            'firstContract has incorrect Property'
        );
        $this->assertEquals(
            $unitWithSameName->getId(),
            $lastContract->getUnit()->getId(),
            'lastContract has incorrect Unit'
        );
        $this->assertEquals(
            $unitWithSameName->getProperty()->getId(),
            $lastContract->getProperty()->getId(),
            'lastContract has incorrect Property'
        );

        $this->assertEquals(
            $unitWithSameName->getId(),
            $contractWaiting->getUnit()->getId(),
            'contractWaiting has incorrect Unit'
        );
        $this->assertEquals(
            $unitWithSameName->getProperty()->getId(),
            $contractWaiting->getProperty()->getId(),
            'contractWaiting has incorrect Property'
        );
    }

    /**
     * @test
     */
    public function shouldNotDeduplicateIfTurnOnDryRunMode()
    {
        $this->load(true);

        $unit = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1);
        $contracts = $unit->getContracts();

        $this->assertGreaterThan(1, count($contracts));
        /**
         * @var Contract $firstContract
         */
        $firstContract = $contracts->first();
        /**
         * @var Contract $lastContract
         */
        $lastContract = $contracts->last();
        /**
         * @var ContractWaiting $contractWaiting
         */
        $contractWaiting = $unit->getContractsWaiting()->first();
        $this->executeCommandTester(
            new UnitDeduplicateCommand(),
            [
                '--src-unit-id' => 1,
                '--dst-property-id' => 18,
                '--dry-run' => 1
            ]
        );

        $this->assertNotEmpty(
            $this->getEntityManager()->getRepository('RjDataBundle:Unit')->find(1),
            'srcUnit is deleted in dryRun mode.'
        );

        $this->getEntityManager()->refresh($firstContract);
        $this->getEntityManager()->refresh($lastContract);
        $this->getEntityManager()->refresh($contractWaiting);
        $this->assertEquals(
            $unit->getId(),
            $firstContract->getUnit()->getId(),
            'firstContract is updated in dryRun mode.'
        );
        $this->assertEquals(
            $unit->getId(),
            $lastContract->getUnit()->getId(),
            'lastContract is updated in dryRun mode.'
        );
        $this->assertEquals(
            $unit->getId(),
            $contractWaiting->getUnit()->getId(),
            'contractWaiting is updated in dryRun mode.'
        );
    }
}
