<?php

namespace RentJeeves\TestBundle\Stubs;

use Psr\Log\LoggerInterface;

/**
 *
 * This is a stub class for testing.
 * It is intended to be injected into other classes that require a LoggerInterface.
 *
 * It current allows you avoid having "noisy" tests by not logging to stdout
 * You can also use the getTotal() method to verify that given log methods have been called and how many times.
 *
 * We might want to extend this in the future to enable logging for easier debugging of tests.
 * Another idea is to also capture the log messages so you can verify specific log message don't change.
 *
 * Class LoggerStub
 * @package RentJeeves\TestBundle\Stubs
 */
class LoggerStub implements LoggerInterface
{

    /**
     * @var array
     */
    private $totals = [];

    /**
     *  Create a new LoggerStub with all counters at 0
     */
    public function __contruct()
    {
        $this->clearCounters();
    }

    /**
     * Clear out all the counters so they will return the default of 0
     */
    public function clearCounters()
    {
        $this->totals = [];
    }

    /**
     *
     * Get the total count for a given counter name.
     *
     * If the counterName is not found it will return 0.
     *
     * @param $counterName
     * @return int
     */
    public function getTotal($counterName)
    {
        if (array_key_exists($counterName, $this->totals)) {

            return $this->totals[$counterName];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        // TODO: Implement emergency() method.
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        $this->increment('alert');
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        // TODO: Implement critical() method.
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        // TODO: Implement error() method.
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        $this->increment('warning');
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        // TODO: Implement notice() method.
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        // TODO: Implement info() method.
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        // TODO: Implement debug() method.
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
    }

    /**
     *
     * Increment a counter
     *
     * It will increment the given $counterName if it exists.
     * It will initialize the given $counterName to 1 if it doesn't exist.
     *
     * @param $counterName
     */
    private function increment($counterName)
    {
        if (array_key_exists($counterName, $this->totals)) {
            $this->totals[$counterName]++;
        } else {
            $this->totals[$counterName] = 1;
        }
    }
}
