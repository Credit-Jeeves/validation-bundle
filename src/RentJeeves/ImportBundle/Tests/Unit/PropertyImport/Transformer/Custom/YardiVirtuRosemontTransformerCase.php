<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Transformer\Custom;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\Yardi\UnitInformation;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\Property;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationAddress;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationFullUnit;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformationUnit;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\UnitInformation as Information;
use RentJeeves\ImportBundle\PropertyImport\Transformer\Custom\YardiVirtuRosemontTransformer;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\DataBundle\Entity\Import;

class YardiVirtuRosemontTransformerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldCheckAddress()
    {
        $logger = $this->getLoggerMock();
        $em = $this->getEntityManagerMock();
        $unitInfo = new UnitInformation();
        $unitInfo->setUnit(new UnitInformationFullUnit());
        $unitInfo->getUnit()->setUnitId('Test');
        $address = new UnitInformationAddress();
        $address->setAddress1('Fishermans Dr');
        $unitInfo->getUnit()->setUnit($unit = new UnitInformationUnit());
        $unitInfo->getUnit()->getUnit()->setInformation($information = new Information());
        $information->setAddress($address);
        $property = new Property();
        $unitInfo->setProperty($property);

        $transformer = new YardiVirtuRosemontTransformer($em, $logger);
        $import = new Import();
        $import->setGroup(new Group());
        $transformer->transformData([$unitInfo], $import);
        $this->assertCount(1, $import->getImportProperties());
        /** @var ImportProperty $importProperty */
        $importProperty = $import->getImportProperties()->get(0);
        $this->assertEquals('Fishermans Dr', $importProperty->getAddress1(), 'Address should map correct');
        $this->assertEmpty($importProperty->getUnitName(), 'We should don\'t have unit name for that transformer');
    }
}
