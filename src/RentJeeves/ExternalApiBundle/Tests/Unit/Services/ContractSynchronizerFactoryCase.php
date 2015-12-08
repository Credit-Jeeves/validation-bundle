<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services;

use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Services\ContractSynchronizerFactory;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ContractSynchronizerFactoryCase extends UnitTestBase
{
    /**
     * @var ContractSynchronizerFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();
        $this->factory = new ContractSynchronizerFactory([
            ApiIntegrationType::MRI => $this->getMockBuilder(
                    'RentJeeves\ExternalApiBundle\Services\MRI\ContractSynchronizer'
                )
                ->disableOriginalConstructor()
                ->getMock(),
            ApiIntegrationType::RESMAN => $this->getMockBuilder(
                    'RentJeeves\ExternalApiBundle\Services\ResMan\ContractSynchronizer'
                )
                ->disableOriginalConstructor()
                ->getMock(),
            ApiIntegrationType::YARDI_VOYAGER => $this->getMockBuilder(
                    'RentJeeves\ExternalApiBundle\Services\Yardi\ContractSynchronizer'
                )
                ->disableOriginalConstructor()
                ->getMock(),
            ApiIntegrationType::AMSI => $this->getMockBuilder(
                    'RentJeeves\ExternalApiBundle\Services\AMSI\ContractSynchronizer'
                )
                ->disableOriginalConstructor()
                ->getMock(),
        ]);
    }

    /**
     * @return array
     */
    public function mappingServiceByAccountingNameDataProvider()
    {
        return [
            [ApiIntegrationType::MRI, 'RentJeeves\ExternalApiBundle\Services\MRI\ContractSynchronizer'],
            [ApiIntegrationType::RESMAN, 'RentJeeves\ExternalApiBundle\Services\ResMan\ContractSynchronizer'],
            [ApiIntegrationType::YARDI_VOYAGER, 'RentJeeves\ExternalApiBundle\Services\Yardi\ContractSynchronizer'],
            [ApiIntegrationType::AMSI, 'RentJeeves\ExternalApiBundle\Services\AMSI\ContractSynchronizer'],
        ];
    }

    /**
     * @param string $accountingSystem
     * @param string $contractSynchronizerClass
     *
     * @test
     * @dataProvider mappingServiceByAccountingNameDataProvider
     */
    public function shouldReturnServiceByAccountingName($accountingSystem, $contractSynchronizerClass)
    {
        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\ContractSynchronizerInterface',
            $this->factory->getSynchronizer($accountingSystem),
            'ContractSynchronizerFactory should return ContractSynchronizer instance' .
            ' and it should implement ContractSynchronizerInterface'
        );

        $this->assertInstanceOf(
            $contractSynchronizerClass,
            $this->factory->getSynchronizer($accountingSystem),
            sprintf(
                'ContractSynchronizerFactory for %s should return ContractSynchronizer instance of %s',
                $accountingSystem,
                $contractSynchronizerClass
            )
        );
    }

    /**
     * @param string $accountingSystem
     * @param string $contractSynchronizerClass
     *
     * @test
     * @dataProvider mappingServiceByAccountingNameDataProvider
     */
    public function shouldReturnServiceByHolding($accountingSystem, $contractSynchronizerClass)
    {
        $holding = $this->getMock('CreditJeeves\DataBundle\Entity\Holding');
        $holding->expects($this->any())->method('getApiIntegrationType')->willReturn($accountingSystem);

        $this->assertInstanceOf(
            'RentJeeves\ExternalApiBundle\Services\ContractSynchronizerInterface',
            $this->factory->getSynchronizerByHolding($holding),
            'ContractSynchronizerFactory should return ContractSynchronizer instance' .
            ' and it should implement ContractSynchronizerInterface'
        );

        $this->assertInstanceOf(
            $contractSynchronizerClass,
            $this->factory->getSynchronizer($accountingSystem),
            sprintf(
                'ContractSynchronizerFactory for %s should return ContractSynchronizer instance of %s',
                $accountingSystem,
                $contractSynchronizerClass
            )
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIncorrectAccountingSystem()
    {
        $this->factory->getSynchronizer('incorrect_system');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function shouldThrowExceptionNonSupportAccountingSystem()
    {
        $this->factory->getSynchronizer(ApiIntegrationType::NONE);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldThrowExceptionHoldingNoneAccountSystem()
    {
        $holding = $this->getMock('CreditJeeves\DataBundle\Entity\Holding');
        $holding->expects($this->any())->method('getApiIntegrationType')->willReturn(ApiIntegrationType::NONE);
        $this->factory->getSynchronizerByHolding($holding);
    }
}
