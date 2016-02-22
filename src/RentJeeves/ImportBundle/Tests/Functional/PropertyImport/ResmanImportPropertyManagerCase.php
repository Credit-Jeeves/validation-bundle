<?php

namespace RentJeeves\ImportBundle\Tests\Functional\PropertyImport;

use Guzzle\Http\Message\Response;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ExternalApiBundle\Tests\Services\ResMan\ResManClientCase;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ResmanImportPropertyManagerCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldImportDataFromResman()
    {
        $this->load(true);
        /** Create MOCK for response from Resman */
        $body = $this->getBaseMock('Guzzle\Http\EntityBody');
        $body->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue($this->getResponseMock()));

        $responseMock = new Response(200, null, $body);

        $httpClientMock = $this->getHttpClientMock();
        $httpClientMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($responseMock));

        $this->getContainer()->set('guzzle_client', $httpClientMock);
        /** Response is mocked */

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);
        $admin = $this->getEntityManager()->getRepository('DataBundle:Admin')->find(1);
        $holding = $group->getHolding();

        $holding->setAccountingSystem(AccountingSystem::RESMAN);

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

        $this->getImportPropertyManager()->import($newImport, ResManClientCase::EXTERNAL_PROPERTY_ID);

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

        $this->assertEquals(
            $countImportPropertyBeforeImport + 260, // 260 unique records(unique extUnitId) from response
            $countImportPropertiesAfterImport,
            'Not all ImportProperties are created.'
        );
        $this->assertEquals(
            $countAllPropertiesBeforeImport + 3, // have 3 different addresses
            $countAllPropertiesAfterImport,
            'Property is not created.'
        );
        $this->assertEquals(
            $countAllPropertyAddressesBeforeImport + 3, // 3 new PropertyAddress
            $countAllPropertiesAddressAfterImport,
            'PropertyAddress is not created.'
        );
        $this->assertEquals(
            $countAllUnitsBeforeImport + 260, // 260 new Units
            $countAllUnitsAfterImport,
            'All Units are not created.'
        );
        $this->assertEquals(
            $countAllUnitMappingsBeforeImport + 260, // 260 new Units
            $countAllUnitMappingsAfterImport,
            'All UnitMappings is not created.'
        );
    }

    /**
     * @return string XML data from MRI
     */
    protected function getResponseMock()
    {
        $pathToFile = $this->getFileLocator()->locate('@ImportBundle/Tests/Fixtures/ResmanResponseForExtProperty.xml');

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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Guzzle\Http\Client
     */
    protected function getHttpClientMock()
    {
        return $this->getMock(
            'Guzzle\Http\Client',
            ['send'],
            [],
            '',
            true
        );
    }
}
