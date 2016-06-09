<?php

namespace RentJeeves\ImportBundle\Tests\Functional\PropertyImport;

use RentJeeves\ImportBundle\ImportSettingsProvider;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ImportPropertySettingsProviderCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldReturnExtPropertyIdsForYardi()
    {
        $this->load(true);
        $group = $this->getEntityManager()->find('DataBundle:Group', 24);

        $yardiSettings = $group->getHolding()->getYardiSettings();
        $yardiSettings->setUrl('https://www.iyardiasp.com/8223thirdparty708dev/');
        $yardiSettings->setUsername('renttrackws');
        $yardiSettings->setPassword('57742');
        $yardiSettings->setDatabaseName('afqoml_70dev');
        $yardiSettings->setDatabaseServer('sdb17\SQL2k8_R2');
        $yardiSettings->setPlatform('SQL Server');

        $group->getImportSettings()->setApiPropertyIds(ImportSettingsProvider::YARDI_ALL_EXTERNAL_PROPERTY_IDS);

        $this->getEntityManager()->flush();
        // mocking response
        $soapClient = $this->getBaseMock('\RentJeeves\ExternalApiBundle\Soap\SoapClient');
        $soapClient->expects($this->once())
            ->method('__soapCall')
            ->will($this->returnValue($this->getResponseMock()));
        $soapClientBuilder = $this->getBaseMock('RentJeeves\ExternalApiBundle\Soap\SoapClientBuilder');
        $soapClientBuilder->expects($this->once())
            ->method('build')
            ->will($this->returnValue($soapClient));
        $this->getContainer()->set('besimple.soap.client.yardi_resident_transactions', $soapClientBuilder);

        $provider = $this->getContainer()->get('import.property.settings_provider');
        $result = $provider->provideExternalPropertyIds($group);
        $this->assertEquals(3, count($result), 'Provider returned incorrect result.');
        $this->assertEquals('rnttrk01', $result[0], 'Provider returned incorrect 1st property.');
        $this->assertEquals('rnttrk02', $result[1], 'Provider returned incorrect 2nd property.');
        $this->assertEquals('rnttrk03', $result[2], 'Provider returned incorrect 3rd property.');
    }

    /**
     * @return \stdClass
     */
    protected function getResponseMock()
    {
        $pathToFile = $this->getFileLocator()->locate(
            '@ImportBundle/Tests/Fixtures/YardiResponseForPropertyConfigurations.xml'
        );

        $data = file_get_contents($pathToFile);

        $response = new \stdClass();
        $response->GetPropertyConfigurationsResult = new \stdClass();
        $response->GetPropertyConfigurationsResult->any = $data;

        return $response;
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
