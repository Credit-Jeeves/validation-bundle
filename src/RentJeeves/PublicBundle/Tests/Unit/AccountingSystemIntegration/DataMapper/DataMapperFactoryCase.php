<?php

namespace RentJeeves\PublicBundle\Tests\Unit\AccountingSystemIntegration\DataMapper;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\DataMapperFactory;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class DataMapperFactoryCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Accounting system type is invalid.
     */
    public function shouldThrowExceptionOnInvalidAccountingSystem()
    {
        $factory = new DataMapperFactory([]);

        $factory->getMapper('invalidSystem');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Accounting system "mri" does not support mapping payment integration data.
     */
    public function shouldThrowExceptionIfMapperNotFound()
    {
        $factory = new DataMapperFactory([]);

        $factory->getMapper(AccountingSystem::MRI);
    }

    /**
     * @test
     */
    public function shouldReturnCorrectMapper()
    {
        $resmanDataMapper = $this->getBaseMock(
            'RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\ResmanDataMapper'
        );

        $mriDataMapper = $this->getBaseMock(
            'RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\MriDataMapper'
        );
        $factory = new DataMapperFactory([
            AccountingSystem::RESMAN => $resmanDataMapper,
            AccountingSystem::MRI => $mriDataMapper,
        ]);

        $mapper = $factory->getMapper(AccountingSystem::RESMAN);

        $this->assertInstanceOf(
            'RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\ResmanDataMapper',
            $mapper,
            'Should return correct mapper.'
        );
    }
}