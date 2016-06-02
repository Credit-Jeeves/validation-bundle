<?php
namespace RentJeeves\TestBundle\Tests\FixtureGenerator\Functional\Services;

use RentJeeves\TestBundle\FixtureGenerator\Services\FixtureFinder;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class FixtureFinderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnFixturesArrayWhenSetCorrectPath()
    {
        $kernel = $this->getKernel();
        $fixtureFinder = new FixtureFinder($kernel);
        $fixtures = $fixtureFinder->getFixtures('@RjTestBundle/Resources/AliceFixtures/');
        $this->assertNotEmpty($fixtures, 'Expected array with element');
    }

    /**
     * @test
     */
    public function shouldReturnFixturesArrayWhenSetCorrectParams()
    {
        $kernel = $this->getKernel();
        $fixtureFinder = new FixtureFinder($kernel);
        $fixtures = $fixtureFinder->getFixtures('@RjTestBundle/Resources/AliceFixtures/', ['test.yml']);
        $this->assertNotEmpty($fixtures, 'Expected array with filename');
        $this->assertEquals(1, count($fixtures), 'Array should be has 1 element');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path not found
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
}
