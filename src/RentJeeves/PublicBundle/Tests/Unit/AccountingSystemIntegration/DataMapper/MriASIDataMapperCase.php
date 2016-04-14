<?php

namespace RentJeeves\PublicBundle\Tests\Unit\AccountingSystemIntegration\DataMapper;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\MriASIDataMapper;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class MriASIDataMapperCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digest is invalid
     */
    public function shouldValidateDigest()
    {
        $validator = $this->getBaseMock('Symfony\Component\Validator\Validator');

        $hmacGenerator = $this->getBaseMock('RentJeeves\CoreBundle\Services\HMACGenerator');

        $hmacGenerator
            ->expects($this->once())
            ->method('validateHMAC')
            ->willReturn(false);

        $dataMapper = new MriASIDataMapper($validator, $hmacGenerator, $this->getLoggerMock());

        $request = $this->getBaseMock('Symfony\Component\HttpFoundation\Request');

        $parameterBag = $this->getBaseMock('Symfony\Component\HttpFoundation\ParameterBag');

        $parameterBag->method('all')->willReturn([]);

        $request->request = $parameterBag;

        $dataMapper->mapData($request);
    }

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

        $hmacGenerator = $this->getBaseMock('RentJeeves\CoreBundle\Services\HMACGenerator');

        $hmacGenerator
            ->expects($this->once())
            ->method('validateHMAC')
            ->willReturn(true);

        $dataMapper = new MriASIDataMapper($validator, $hmacGenerator, $this->getLoggerMock());

        $request = $this->getBaseMock('Symfony\Component\HttpFoundation\Request');

        $parameterBag = $this->getBaseMock('Symfony\Component\HttpFoundation\ParameterBag');

        $parameterBag->method('all')->willReturn([]);

        $request->request = $parameterBag;

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

        $hmacGenerator = $this->getBaseMock('RentJeeves\CoreBundle\Services\HMACGenerator');

        $hmacGenerator
            ->expects($this->once())
            ->method('validateHMAC')
            ->willReturn(true);

        $hmacGenerator
            ->expects($this->once())
            ->method('generateHMAC')
            ->willReturn('Digest_Test');

        $dataMapper = new MriASIDataMapper($validator, $hmacGenerator, $this->getLoggerMock());

        $request = $this->getBaseMock('Symfony\Component\HttpFoundation\Request');

        $parameterBag = $this->getBaseMock('Symfony\Component\HttpFoundation\ParameterBag');

        $parameterBag
            ->method('all')
            ->willReturn([]);

        $request->request = $parameterBag;

        $request
            ->method('get')
            ->willReturnCallback(function ($param) {
                switch ($param) {
                    case 'trackingid':
                        return '1111';
                    case 'appfee':
                    case 'secdep':
                        return 100;
                }
            });

        $integratedModel = $dataMapper->mapData($request);

        $this->assertInstanceOf(
            'RentJeeves\PublicBundle\AccountingSystemIntegration\ASIIntegratedModel',
            $integratedModel,
            'Should be returned ASIIntegratedModel'
        );

        $this->assertEquals(
            AccountingSystem::MRI,
            $integratedModel->getAccountingSystem(),
            'Should be mapped accounting system resman'
        );

        $this->assertEquals(
            [
                'trackingid' => '1111',
                'apipost' => 'true',
                'sum' => '200.00',
                'Digest' => 'Digest_Test'
            ],
            $integratedModel->getReturnParams(),
            'Should be mapped additional return params'
        );
    }
}
