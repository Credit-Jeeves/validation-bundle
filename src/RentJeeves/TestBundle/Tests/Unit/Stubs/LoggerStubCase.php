<?php

namespace RentJeeves\TestBundle\Tests\Unit\Stubs;

use RentJeeves\TestBundle\Stubs\LoggerStub;

class LoggerStubCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canBeConstructed()
    {
        new LoggerStub();
    }

    /**
     * @test
     */
    public function shouldReturnZero()
    {
        $stub = new LoggerStub();
        $this->assertEquals(0, $stub->getTotal("alert"), "fresh logger stub should return zero totals");
    }

    /**
     * @test
     */
    public function shouldIncrementAlert()
    {
        $stub = new LoggerStub();
        $stub->alert("I love stubs!");
        $this->assertEquals(1, $stub->getTotal("alert"), "alert counter not incrementing");
    }

    /**
     * @test
     */
    public function shouldIncrementWarning()
    {
        $stub = new LoggerStub();
        $stub->warning("I love stubs!");
        $this->assertEquals(1, $stub->getTotal("warning"), "alert counter not incrementing");
    }
}
