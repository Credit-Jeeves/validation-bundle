<?php

namespace RentJeeves\TestBundle\Tests\Unit\FixtureGenerator\Services;

use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\Persister\Doctrine;
use RentJeeves\TestBundle\FixtureGenerator\Services\FixtureLoader;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class FixtureLoaderCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnEmptyArrayWhenSetEmptyParams()
    {
        $aliceLoaderMock = $this->getMock(Loader::class);
        $alicePersisterMock = $this->getMockBuilder(Doctrine::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fixtures = [];
        $loadManager = new FixtureLoader($aliceLoaderMock, $alicePersisterMock);
        $objects = $loadManager->load(
            $fixtures,
            function ($message) {
                echo $message;
            }
        );
        $this->assertEmpty($objects, 'Array should be empty');
    }

    /**
     * @test
     */
    public function shouldReturnObjectsWhenParamsNotEmpty()
    {
        $aliceLoaderMock = $this->getMock(Loader::class);
        $aliceLoaderMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue([1, 2])); // Emulate 2 entities from file
        $alicePersisterMock = $this->getMockBuilder(Doctrine::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fixtures = ['test.yml'];
        $loadManager = new FixtureLoader($aliceLoaderMock, $alicePersisterMock);
        $objects = $loadManager->load(
            $fixtures,
            function ($message) {
                echo $message;
            }
        );

        $this->assertNotEmpty($objects, 'Objects should not be empty');
        $this->assertEquals(2, count($objects), 'Array should have 2 elements');
    }
}
