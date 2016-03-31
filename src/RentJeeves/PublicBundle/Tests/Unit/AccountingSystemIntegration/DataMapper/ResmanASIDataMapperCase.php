<?php

namespace RentJeeves\PublicBundle\Tests\Unit\AccountingSystemIntegration\DataMapper;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\ResmanASIDataMapper;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class ResmanASIDataMapperCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Test Error
     */
    public function shouldValidateModel()
    {
        $validator = $this->getBaseMock('Symfony\Component\Validator\Validator');

        $error = $this->getBaseMock('Symfony\Component\Validator\ConstraintViolation');

        $error
            ->expects($this->once())
            ->method('getMessage')
            ->willReturn('Test Error');

        $errorList = $this->getBaseMock('Symfony\Component\Validator\ConstraintViolationList');

        $errorList
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $errorList
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$error]));


        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(
                $errorList
            );

        $dataMapper = new ResmanASIDataMapper($validator);

        $request = $this->getBaseMock('Symfony\Component\HttpFoundation\Request');

        $dataMapper->mapData($request);
    }

    /**
     * @test
     */
    public function shouldMappedData()
    {
        $validator = $this->getBaseMock('Symfony\Component\Validator\Validator');

        $errorList = $this->getBaseMock('Symfony\Component\Validator\ConstraintViolationList');

        $errorList
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);

        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(
                $errorList
            );

        $dataMapper = new ResmanASIDataMapper($validator);

        $request = $this->getBaseMock('Symfony\Component\HttpFoundation\Request');

        $integratedModel = $dataMapper->mapData($request);

        $this->assertInstanceOf(
            'RentJeeves\PublicBundle\AccountingSystemIntegration\ASIIntegratedModel',
            $integratedModel,
            'Should be returned ASIIntegratedModel'
        );

        $this->assertEquals(
            AccountingSystem::RESMAN,
            $integratedModel->getAccountingSystem(),
            'Should be mapped accounting system resman'
        );

        $this->assertEquals(
            ['success' => 'true'],
            $integratedModel->getReturnParams(),
            'Should be mapped additional return params'
        );
    }
}
