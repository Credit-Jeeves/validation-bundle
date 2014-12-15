<?php

namespace RentJeeves\ExternalApiBundle\Traits;

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
        if (!$this->isDebugEnabled()) {
            return;
        }
        echo "\n";
        var_dump($var);
        echo "\n";
    }
}
