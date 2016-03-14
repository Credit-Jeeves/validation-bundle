<?php

namespace RentJeeves\ImportBundle\Tests\Unit\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\CoreBundle\Services\AddressLookup\SmartyStreetsAddressLookupService;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ImportBundle\PropertyImport\Transformer\CsvTransformer;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class CsvTransformerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    use WriteAttributeExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportTransformerException
     * @expectedExceptionMessage Input array should contain not empty "hashHeader".
     */
    public function shouldThrowExceptionIfInputDataDoesNotContainHeadHash()
    {
        $transformer = new CsvTransformer(
            $this->getEntityManagerMock(),
            $this->getSSLookupService(),
            $this->getLoggerMock()
        );

        $transformer->transformData([], new Import());
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportTransformerException
     * @expectedExceptionMessage Group#1 doesn`t have importMapping for hash = "test"
     */
    public function shouldThrowExceptionIfGroupDoesNotHaveImportMappingForCurrentHashHeader()
    {
        $import = new Import();
        $import->setGroup($group = new Group());
        $this->writeIdAttribute($group, 1);

        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['group' => $group, 'headerHash' => 'test']))
            ->willReturn(null);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $transformer = new CsvTransformer(
            $em,
            $this->getSSLookupService(),
            $this->getLoggerMock()
        );

        $transformer->transformData(['hashHeader' => 'test'], $import);
    }

    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportTransformerException
     * @expectedExceptionMessage ImportMapping doesn`t contain mapping for required field(s): street, city, zip, state
     */
    public function shouldThrowExceptionIfImportMappingDoesNotContainRequiredFields()
    {
        $import = new Import();
        $import->setGroup($group = new Group());
        $this->writeIdAttribute($group, 1);

        $mapping = new ImportMappingChoice();
        $mapping->setMappingData(serialize(['test' => 'test']));

        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['group' => $group, 'headerHash' => 'test']))
            ->willReturn($mapping);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $transformer = new CsvTransformer(
            $em,
            $this->getSSLookupService(),
            $this->getLoggerMock()
        );

        $transformer->transformData(['hashHeader' => 'test'], $import);
    }

    /**
     * @test
     */
    public function shouldCreateImportProperty()
    {
        $import = new Import();
        $import->setGroup($group = new Group());
        $this->writeIdAttribute($group, 1);
        $inputData = [
            'hashHeader' => 'test',
            'data' => [
                ['testStreet', 'testCity', 'testZip', 'testState', 'extra', 'testUnit'],
            ]
        ];

        $address = new Address();
        $address->setStreet('ssStreet');
        $address->setCity('ssCity');
        $address->setZip('ssZip');
        $address->setState('ssState');
        $address->setUnitName('ssUnit');

        $lookupService = $this->getSSLookupService();
        $lookupService->expects($this->once())
            ->method('lookupFreeform')
            ->with($this->equalTo('testStreet testUnit, testCity, testState, testZip'))
            ->willReturn($address);

        $mapping = new ImportMappingChoice();
        $mappingData = [
            1 => 'street',
            2 => 'city',
            3 => 'zip',
            4 => 'state',
            5 => 'extraField',
            6 => 'unit'
        ];
        $mapping->setMappingData(serialize($mappingData));

        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['group' => $group, 'headerHash' => 'test']))
            ->willReturn($mapping);

        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($repositoryMock);
        $em->expects($this->once())
            ->method('persist')
            ->with(
                $this->callback(function (ImportProperty $subject) {
                    return
                        $subject->isAddressHasUnits() == true &&
                        $subject->getUnitName() == 'ssUnit' && // only unit name from SS response
                        $subject->getAddress1() == 'testStreet' &&
                        $subject->getCity() == 'testCity' &&
                        $subject->getState() == 'testState' &&
                        $subject->getZip() == 'testZip';
                })
            );

        $transformer = new CsvTransformer(
            $em,
            $lookupService,
            $this->getLoggerMock()
        );

        $transformer->transformData($inputData, $import);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SmartyStreetsAddressLookupService
     */
    protected function getSSLookupService()
    {
        return $this->getBaseMock(SmartyStreetsAddressLookupService::class);
    }
}
