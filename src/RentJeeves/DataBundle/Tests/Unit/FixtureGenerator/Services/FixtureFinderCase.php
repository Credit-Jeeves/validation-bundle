<?php

namespace RentJeeves\DataBundle\Tests\Unit\FixtureGenerator\Services;

use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\DataBundle\FixtureGenerator\Services\FixtureFinder;
use Symfony\Component\HttpKernel\KernelInterface;

class FixtureFinderCase extends UnitTestBase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path not found: @TestBundle/InvalidPath/
     */
    public function shouldThrowExceptionIfSetInvalidPath()
    {
        $kernelMock = $this->getMock(KernelInterface::class);
        $kernelMock->expects($this->once())
            ->method('locateResource')
            ->will($this->throwException(new \Exception('Test')));
        $fixtureFinder = new FixtureFinder($kernelMock);
        $fixtureFinder->getFixtures('@TestBundle/InvalidPath/');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Fixture file does not exist: /path/to/fixtures/test.yml
     */
    public function shouldThrowExceptionWhenFixtureIsNotExist()
    {
        $kernelMock = $this->getMock(KernelInterface::class);
        $kernelMock->expects($this->once())
            ->method('locateResource')
            ->will($this->returnValue('/path/to/fixtures/'));
        $fixtureFinder = new FixtureFinder($kernelMock);
        $fixtureFinder->getFixtures('@TestBundle/InvalidPath/', ['test.yml']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find any fixtures to load
     */
    public function shouldThrowExceptionIfFixturesNotFoundInPath()
    {
        $kernelMock = $this->getMock(KernelInterface::class);
        $kernelMock->expects($this->once())
            ->method('locateResource')
            ->will($this->returnValue(__DIR__));
        $fixtureFinder = new FixtureFinder($kernelMock);
        $fixtureFinder->getFixtures('test');
    }

    /**
     * @test
     */
    public function shouldFindFixturesWhenSetCorrectPath()
    {
        $realPath = __DIR__.'/../../../Fixtures/FixtureGenerator/Services/';
        $kernelMock = $this->getMock(KernelInterface::class);
        $kernelMock->expects($this->once())
            ->method('locateResource')
            ->will($this->returnValue($realPath));
        $fixtureFinder = new FixtureFinder($kernelMock);
        $fixtures = $fixtureFinder->getFixtures('test');
        $this->assertNotEmpty($fixtures, 'Expected array with elements');
    }

    /**
     * @test
     */
    public function shouldFindFixtureWhenSetCorrectFilename()
    {
        $realPath = __DIR__.'/../../../Fixtures/FixtureGenerator/Services/';
        $kernelMock = $this->getMock(KernelInterface::class);
        $kernelMock->expects($this->once())
            ->method('locateResource')
            ->will($this->returnValue($realPath));
        $fixtureFinder = new FixtureFinder($kernelMock);
        $fixtures = $fixtureFinder->getFixtures('test', ['test.yml']);
        $this->assertNotEmpty($fixtures, 'Expected array with filename');
        $this->assertEquals(1, count($fixtures), 'Array should has 1 element');
    }
}
