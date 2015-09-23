<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Helpers;

use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\CoreBundle\Helpers\PeriodicExecutor;

class PeriodicExecutorCase extends UnitTestBase
{
    private $testCounter;

    /** @var PeriodicExecutor $periodicExecutor */
    private $periodicExecutor;

    /**
     * @test
     */
    public function shouldHaveTenExecutionsOutOfOneHundred()
    {
        $this->testCounter = 0;
        $period = 10;
        $callback = 'myCallback';
        $this->periodicExecutor = new PeriodicExecutor($this, $callback, $period, $this->logger);

        for ($i=0; $i<100; $i++) {
            $this->periodicExecutor->increment();
        }

        $this->assertEquals(10, $this->testCounter, "Did not execute the expected number of times");
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function shouldThrowExceptionIfCallbackObjectIsNotAnObject()
    {
        $period = 10;
        $callback = 'myCallback';
        new PeriodicExecutor("NotAnObject", $callback, $period, $this->logger);
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     */
    public function shouldThrowExceptionIfCallbackIsNotCallable()
    {
        $period = 10;
        $callback = 'bogusCallback';
        new PeriodicExecutor($this, $callback, $period, $this->logger);
    }

    /**
     * @test
     * @expectedException BadMethodCallException
     */
    public function shouldThrowExceptionIfCallbackIsPrivate()
    {
        $period = 10;
        $callback = 'privateCallback';
        new PeriodicExecutor($this, $callback, $period, $this->logger);
    }

    public function myCallback()
    {
        $this->testCounter++;
    }

    private function privateCallback()
    {
        $this->testCounter++;
    }
}
