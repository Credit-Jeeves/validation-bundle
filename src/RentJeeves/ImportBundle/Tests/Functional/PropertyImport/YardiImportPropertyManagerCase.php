<?php

namespace RentJeeves\ImportBundle\Tests\Functional\PropertyImport;

use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentDataClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetPropertyConfigurationsResponse;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentDataResponse;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class YardiImportPropertyManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldImportDataFromYardi()
    {
        $this->load(true);

        $residentDataClientMock = $this->getResidentDataClientMock();
        $residentDataClientMock->expects($this->once())
            ->method('getResidentData')
            ->will($this->returnValue($this->getResidentDataResponse()));

        $residentDataClientMock->expects($this->once())
            ->method('getResidents')
            ->will($this->returnValue($this->getResidentsResponse()));

        $residentDataClientMock->expects($this->exactly(2))
            ->method('setSettings');

        $residentDataClientMock->expects($this->exactly(2))
            ->method('build');

        $residentDataClientMock->expects($this->exactly(1))
            ->method('isError');

        $residentTransactionsMock = $this->getResidentTransactionsClientMock();
        $residentTransactionsMock->expects($this->once())
            ->method('getPropertyConfigurations')
            ->will($this->returnValue($this->getPropertyConfigurationsResponse()));

        $residentTransactionsMock->expects($this->once())
            ->method('setSettings');

        $residentTransactionsMock->expects($this->once())
            ->method('build');

        $this->getContainer()->set('soap.client.yardi.resident_transactions', $residentTransactionsMock);
        $this->getContainer()->set('soap.client.yardi.resident_data', $residentDataClientMock);


        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $admin = $this->getEntityManager()->getRepository('DataBundle:Admin')->find(1);
        $newImport = new Import();
        $newImport->setGroup($group);
        $newImport->setImportType(ImportModelType::PROPERTY);
        $newImport->setStatus(ImportStatus::RUNNING);
        $newImport->setUser($admin);

        $this->getEntityManager()->persist($newImport);
        $this->getEntityManager()->flush();

        $this->getImportPropertyManager()->import($newImport, 'rnttrk01');
        $importProperties = $newImport->getImportProperties();
        $this->assertCount(1, $importProperties, 'Should transform response');
        /** @var ImportProperty $importProperty */
        $importProperty = $importProperties->get(0);
        $this->assertEquals('Santa Barbara', $importProperty->getCity(), 'City should map');
        $this->assertEquals('CA', $importProperty->getState(), 'State should map');
        $this->assertEquals('rnttrk01', $importProperty->getExternalPropertyId(), 'ExternalProperyId should map');
        $this->assertEquals('4447 Hollister Ave', $importProperty->getAddress1(), 'Address should map');
        $this->assertEquals('103', $importProperty->getUnitName(), 'Unit name should map');
        $this->assertEquals('rnttrk01||103', $importProperty->getExternalUnitId(), 'Unit ID should map');
        $this->assertEquals('93110', $importProperty->getZip(), 'Zip should map');
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
            ['getPropertyConfigurations', 'setSettings', 'build'],
            [],
            '',
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResidentDataClient
     */
    protected function getResidentDataClientMock()
    {
        return $this->getMock(
            'RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentDataClient',
            ['getResidentData', 'getResidents', 'setSettings', 'build', 'isError'],
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
     * @return GetResidentDataResponse
     */
    protected function getResidentDataResponse()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@ImportBundle/Tests/Fixtures/getResidentDataResponse.xml'
        );

        return $this->deserialize(
            file_get_contents($pathToFile),
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentDataResponse'
        );
    }

    /**
     * @return GetResidentDataResponse
     */
    protected function getResidentsResponse()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@ImportBundle/Tests/Fixtures/getResidentsResponse.xml'
        );

        return $this->deserialize(
            file_get_contents($pathToFile),
            'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\GetResidentsResponse'
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
