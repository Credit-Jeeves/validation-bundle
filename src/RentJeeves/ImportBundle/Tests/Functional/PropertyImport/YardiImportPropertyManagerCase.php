<?php

namespace RentJeeves\ImportBundle\Tests\Functional\PropertyImport;

use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetUnitInformationResponse;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class YardiImportPropertyManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldImportDataFromYardi()
    {
        $this->load(true);

        $residentTransactionsMock = $this->getResidentTransactionsClientMock();
        $residentTransactionsMock->expects($this->once())
            ->method('getPropertyConfigurations')
            ->will($this->returnValue($this->getPropertyConfigurationsResponse()));

        $residentTransactionsMock->expects($this->once())
            ->method('getUnitInformation')
            ->will($this->returnValue($this->getUnitInformationResponse()));

        $residentTransactionsMock->expects($this->exactly(2))
            ->method('setSettings');

        $residentTransactionsMock->expects($this->exactly(2))
            ->method('build');

        $this->getContainer()->set('soap.client.yardi.resident_transactions', $residentTransactionsMock);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $admin = $this->getEntityManager()->getRepository('DataBundle:Admin')->find(1);
        $newImport = new Import();
        $newImport->setGroup($group);
        $newImport->setImportType(ImportModelType::PROPERTY);
        $newImport->setStatus(ImportStatus::RUNNING);
        $newImport->setUser($admin);

        $this->getEntityManager()->persist($newImport);
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

        $this->getImportPropertyManager()->import($newImport, 'rnttrk01');
        $importProperties = $newImport->getImportProperties();
        $this->assertCount(62, $importProperties, 'Should transform response'); // has 66 but 62 unique
        /** @var ImportProperty $importProperty */
        $importProperty = $importProperties->get(0);
        $this->assertEquals('Santa Barbara', $importProperty->getCity(), 'City should map');
        $this->assertEquals('CA', $importProperty->getState(), 'State should map');
        $this->assertEquals('rnttrk01', $importProperty->getExternalPropertyId(), 'ExternalProperyId should map');
        $this->assertEquals('4447 Hollister Ave', $importProperty->getAddress1(), 'Address should map');
        $this->assertEquals('106', $importProperty->getUnitName(), 'Unit name should map');
        $this->assertEquals('rnttrk01||106', $importProperty->getExternalUnitId(), 'Unit ID should map');
        $this->assertEquals('93110', $importProperty->getZip(), 'Zip should map');
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
            $countImportPropertyBeforeImport + 62, // 62 unique records(unique extUnitId) from response
            $countImportPropertiesAfterImport,
            'Not all ImportProperties are created.'
        );
        $this->assertEquals(
            $countAllPropertiesBeforeImport + 1, // 1 new Property
            $countAllPropertiesAfterImport,
            'Property is not created.'
        );
        $this->assertEquals(
            $countAllPropertyAddressesBeforeImport + 1, // 1 new PropertyAddress
            $countAllPropertiesAddressAfterImport,
            'PropertyAddress is not created.'
        );
        $this->assertEquals(
            $allPropertyGroups + 1, // 1 new PropertyGroup
            $allPropertyGroupsAfterImport,
            'PropertyGroup is not created.'
        );
        $this->assertEquals(
            $countAllUnitsBeforeImport + 62, // 62 new Unit
            $countAllUnitsAfterImport,
            'Unit is not created.'
        );
        $this->assertEquals(
            $countAllUnitMappingsBeforeImport + 62, // 62 new Unit Mapping
            $countAllUnitMappingsAfterImport,
            'Unit Mappint is not created.'
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
     * @return \PHPUnit_Framework_MockObject_MockObject|ResidentTransactionsClient
     */
    protected function getResidentTransactionsClientMock()
    {
        return $this->getMock(
            'RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient',
            ['getPropertyConfigurations', 'setSettings', 'build', 'getUnitInformation'],
            [],
            '',
            '',
            false
        );
    }

    /**
     * @return Property[]
     */
    protected function getPropertyConfigurationsResponse()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@ImportBundle/Tests/Fixtures/getPropertyConfigurationsResponse.xml'
        );
        /** @var GetPropertyConfigurationsResponse $getPropertyConfigurationsResponse */
        $getPropertyConfigurationsResponse = $this->deserialize(
            file_get_contents($pathToFile),
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse'
        );

        return $getPropertyConfigurationsResponse;
    }

    /**
     * @return GetUnitInformationResponse
     */
    protected function getUnitInformationResponse()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@ImportBundle/Tests/Fixtures/getUnitInformationResponse.xml'
        );

        return $this->deserialize(
            file_get_contents($pathToFile),
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetUnitInformationResponse'
        );
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }

    protected function deserialize($data, $class)
    {
        $serializer = $this->getContainer()->get('jms_serializer');

        return $serializer->deserialize(
            $data,
            $class,
            'xml'
        );
    }
}
