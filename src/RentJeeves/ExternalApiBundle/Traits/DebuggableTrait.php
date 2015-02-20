<?php

namespace RentJeeves\ExternalApiBundle\Traits;

use Monolog\Logger;

trait DebuggableTrait
{
    /**
     * @var boolean
     */
    protected $debug = false;

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    public function isDebugEnabled()
    {
        return $this->debug;
    }

    /**
     * @param $var
     */
    public function debugMessage($var)
    {
        if (property_exists(get_class($this), 'logger') && $this->logger instanceof Logger) {
            $this->logger->debug($var);
        }

        if (!$this->isDebugEnabled()) {
            return;
        }
        echo "\n";
        print_r($var);
        echo "\n";
    }
}
