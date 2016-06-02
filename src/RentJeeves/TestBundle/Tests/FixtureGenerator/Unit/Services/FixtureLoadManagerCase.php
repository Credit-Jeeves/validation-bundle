<?php

namespace RentJeeves\TestBundle\Tests\FixtureGenerator\Unit\Services;

use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\TestBundle\FixtureGenerator\Services\FixtureLoadManager;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class FixtureLoadManagerCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnEmptyArrayWhenSetEmptyParams()
    {
        $emMock = $this->getMock(EntityManagerInterface::class);
        $fixtures = [];
        $loadManager = new FixtureLoadManager($emMock);
        $objects = $loadManager->load(
            $fixtures,
            function ($message) {
                echo $message;
            }
        );
        $this->assertEmpty($objects, 'Array should be empty');
    }
}
