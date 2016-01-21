<?php

namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services;

use RentJeeves\DataBundle\Enum\AccountingSystem;
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
            AccountingSystem::MRI => $this->getMockBuilder(
                'RentJeeves\ExternalApiBundle\Services\MRI\ContractSynchronizer'
            )
            ->disableOriginalConstructor()
            ->getMock(),
            AccountingSystem::RESMAN => $this->getMockBuilder(
                'RentJeeves\ExternalApiBundle\Services\ResMan\ContractSynchronizer'
            )
            ->disableOriginalConstructor()
            ->getMock(),
            AccountingSystem::YARDI_VOYAGER => $this->getMockBuilder(
                'RentJeeves\ExternalApiBundle\Services\Yardi\ContractSynchronizer'
            )
            ->disableOriginalConstructor()
            ->getMock(),
            AccountingSystem::AMSI => $this->getMockBuilder(
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
            [AccountingSystem::MRI, 'RentJeeves\ExternalApiBundle\Services\MRI\ContractSynchronizer'],
            [AccountingSystem::RESMAN, 'RentJeeves\ExternalApiBundle\Services\ResMan\ContractSynchronizer'],
            [AccountingSystem::YARDI_VOYAGER, 'RentJeeves\ExternalApiBundle\Services\Yardi\ContractSynchronizer'],
            [AccountingSystem::AMSI, 'RentJeeves\ExternalApiBundle\Services\AMSI\ContractSynchronizer'],
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
        $holding->expects($this->any())->method('getAccountingSystem')->willReturn($accountingSystem);

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
        $this->factory->getSynchronizer(AccountingSystem::NONE);
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function shouldThrowExceptionHoldingNoneAccountSystem()
    {
        $holding = $this->getMock('CreditJeeves\DataBundle\Entity\Holding');
        $holding->expects($this->any())->method('getAccountingSystem')->willReturn(AccountingSystem::NONE);
        $this->factory->getSynchronizerByHolding($holding);
    }
}
