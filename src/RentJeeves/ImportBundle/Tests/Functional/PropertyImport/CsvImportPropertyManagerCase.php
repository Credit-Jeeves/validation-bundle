<?php

namespace RentJeeves\ImportBundle\Tests\Functional\PropertyImport;

use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class CsvImportPropertyManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldImportDataFromCsvFile()
    {
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $admin = $this->getEntityManager()->getRepository('DataBundle:Admin')->find(1);

        $group->getImportSettings()->setSource(ImportSource::CSV);

        $newImport = new Import();
        $newImport->setGroup($group);
        $newImport->setImportType(ImportModelType::PROPERTY);
        $newImport->setStatus(ImportStatus::RUNNING);
        $newImport->setUser($admin);

        $newImportMapping = new ImportMappingChoice();
        $newImportMapping->setGroup($group);
        $newImportMapping->setHeaderHash('053311fd769932214aac9719cd111cd4');
        $newImportMapping->setMappingData(
            [
                5 => 'unit_id',
                7 => 'street',
                9 => 'city',
                10 => 'state',
                11 => 'zip',
            ]
        );

        $this->getEntityManager()->persist($newImport);
        $this->getEntityManager()->persist($newImportMapping);
        $this->getEntityManager()->flush();

        $allImportProperties = $this->getEntityManager()->getRepository('RjDataBundle:ImportProperty')->findAll();
        $countImportPropertyBeforeImport = count($allImportProperties);
        $allProperties = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findAll();
        $countAllPropertiesBeforeImport = count($allProperties);
        $allPropertiesAddresses = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->findAll();
        $countAllPropertyAddressesBeforeImport = count($allPropertiesAddresses);
        $allUnits = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findAll();
        $countAllUnitsBeforeImport = count($allUnits);
        $allUnitMappings = $this->getEntityManager()->getRepository('RjDataBundle:UnitMapping')->findAll();
        $countAllUnitMappingsBeforeImport = count($allUnitMappings);

        $allPropertyGroups = $this->getEntityManager()
            ->getConnection()->query('SELECT COUNT(*) as test FROM rj_group_property')->fetchColumn(0);

        $file = $this->getFileLocator()->locate('@ImportBundle/Tests/Fixtures/csvExample.csv');
        $id = $newImport->getId();
        $this->getImportPropertyManager()->import($newImport, $file);
        $importProperties = $this->getEntityManager()->getRepository('RjDataBundle:ImportProperty')
            ->findBy(['import' => $id]);

        $this->assertCount(3, $importProperties, 'Should transform response');
        /** @var ImportProperty $importProperty */
        $importProperty = $importProperties[0];
        $this->assertEquals('Tempe', $importProperty->getCity(), 'City should map');
        $this->assertEquals('AZ', $importProperty->getState(), 'State should map');
        $this->assertNull($importProperty->getExternalPropertyId(), 'ExternalProperyId should be null');
        $this->assertEquals('6830 S Butte', $importProperty->getAddress1(), 'Address should map');
        $this->assertEquals('BU6830-T', $importProperty->getExternalUnitId(), 'Unit ID should map');
        $this->assertEquals('85283', $importProperty->getZip(), 'Zip should map');

        $allImportProperties = $this->getEntityManager()->getRepository('RjDataBundle:ImportProperty')->findAll();
        $countImportPropertiesAfterImport = count($allImportProperties);
        $allProperties = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findAll();
        $countAllPropertiesAfterImport = count($allProperties);
        $allPropertiesAddresses = $this->getEntityManager()->getRepository('RjDataBundle:PropertyAddress')->findAll();
        $countAllPropertiesAddressAfterImport = count($allPropertiesAddresses);
        $allUnits = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findAll();
        $countAllUnitsAfterImport = count($allUnits);
        $allUnitMappings = $this->getEntityManager()->getRepository('RjDataBundle:UnitMapping')->findAll();
        $countAllUnitMappingsAfterImport = count($allUnitMappings);

        $allPropertyGroupsAfterImport = $this->getEntityManager()
            ->getConnection()->query('SELECT COUNT(*) as test FROM rj_group_property')->fetchColumn(0);

        $this->assertEquals(
            $countImportPropertyBeforeImport + 3, // 48 unique records(unique extUnitId) from response
            $countImportPropertiesAfterImport,
            'Not all ImportProperties are created.'
        );
        $this->assertEquals(
            $countAllPropertiesBeforeImport + 3, // 1 new Property
            $countAllPropertiesAfterImport,
            'Property is not created.'
        );
        $this->assertEquals(
            $countAllPropertyAddressesBeforeImport + 3, // 1 new PropertyAddress
            $countAllPropertiesAddressAfterImport,
            'PropertyAddress is not created.'
        );
        $this->assertEquals(
            $allPropertyGroups + 3, // 3 new PropertyGroup
            $allPropertyGroupsAfterImport,
            'PropertyGroup is not created.'
        );
        $this->assertEquals(
            $countAllUnitsBeforeImport + 3, // 48 new Unit
            $countAllUnitsAfterImport,
            'Unit is not created.'
        );
    }

    /**
     * @return \RentJeeves\ImportBundle\PropertyImport\ImportPropertyManager
     */
    protected function getImportPropertyManager()
    {
        return $this->getContainer()->get('import.property.manager');
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
