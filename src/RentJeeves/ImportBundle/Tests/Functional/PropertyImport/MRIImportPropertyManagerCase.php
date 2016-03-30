<?php

namespace RentJeeves\ImportBundle\Tests\Functional\PropertyImport;

use Guzzle\Http\Message\Response;
use RentJeeves\CoreBundle\HttpClient\HttpClient;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class MRIImportPropertyManagerCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldImportDataFromMRI()
    {
        $this->load(true);
        /** Create MOCK for response from MRI */
        $body = $this->getBaseMock('Guzzle\Http\EntityBodyInterface');
        $body->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue($this->getResponseMock()));

        $responseMock = new Response(200, null, $body);

        $httpClientMock = $this->getHttpClientMock();
        $httpClientMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($responseMock));

        $this->getContainer()->set('http_client', $httpClientMock);
        /** Response is mocked */

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $admin = $this->getEntityManager()->getRepository('DataBundle:Admin')->find(1);
        $holding = $group->getHolding();

        $holding->setAccountingSystem(AccountingSystem::MRI);

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

        $this->getImportPropertyManager()->import($newImport, 500);

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
            $countImportPropertyBeforeImport + 15, // 15 unique records(unique extUnitId) from response
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
            $countAllUnitsBeforeImport + 15, // 15 new Units
            $countAllUnitsAfterImport,
            'PropertyAddress is not created.'
        );
        $this->assertEquals(
            $countAllUnitMappingsBeforeImport + 15, // 15 new Units
            $countAllUnitMappingsAfterImport,
            'PropertyAddress is not created.'
        );
    }

    /**
     * @return string XML data from MRI
     */
    protected function getResponseMock()
    {
        $pathToFile = $this->getFileLocator()->locate('@ImportBundle/Tests/Fixtures/MriResponseForProperty500.xml');

        return file_get_contents($pathToFile);
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClient
     */
    protected function getHttpClientMock()
    {
        return $this->getMock(
            'RentJeeves\CoreBundle\HttpClient\HttpClient',
            ['send'],
            [$this->getContainer()->get('guzzle_client'), $this->getContainer()->get('logger')],
            '',
            true
        );
    }
}
