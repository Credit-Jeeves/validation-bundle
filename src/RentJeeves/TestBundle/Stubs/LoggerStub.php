<?php

namespace RentJeeves\TestBundle\Stubs;

use Psr\Log\LoggerInterface;

class LoggerStub implements LoggerInterface
{

    private $totals = [];

    public function __contruct()
    {
        $this->clearCounters();
    }

    public function clearCounters()
    {
        $this->totals = [
            'warning' => 0,
            'alert' => 0
        ];
    }

    public function getTotal($counterName)
    {
        if (array_key_exists($counterName, $this->totals)) {

            return $this->totals[$counterName];
        }

        return 0;
    }

    public function emergency($message, array $context = array())
    {
        // TODO: Implement emergency() method.
    }

    public function alert($message, array $context = array())
    {
        $this->increment('alert');
    }

    public function critical($message, array $context = array())
    {
        // TODO: Implement critical() method.
    }

    public function error($message, array $context = array())
    {
        // TODO: Implement error() method.
    }

    public function warning($message, array $context = array())
    {
        $this->increment('warning');
    }

    public function notice($message, array $context = array())
    {
        // TODO: Implement notice() method.
    }

    public function info($message, array $context = array())
    {
        // TODO: Implement info() method.
    }

    public function debug($message, array $context = array())
    {
        // TODO: Implement debug() method.
    }

    public function log($level, $message, array $context = array())
    {
        // TODO: Implement log() method.
    }

    private function increment($counterName)
    {
        if (array_key_exists($counterName, $this->totals)) {
            $this->totals[$counterName]++;
        } else {
            $this->totals[$counterName] = 1;
        }
    }

}
