<?php

namespace RentJeeves\ExternalApiBundle\Traits;

trait SoapDebuggableTrait
{
    /**
     * @param bool $show
     * @return array
     */
    public function getFullResponse($show = true)
    {
        return $this->getSoapData('__getLastResponse', $show);
    }

    /**
     * @param bool $show
     * @return array
     */
    public function getFullRequest($show = true)
    {
        return $this->getSoapData('__getLastRequest', $show);
    }

    /**
     * @param string $method
     * @param boolean $show
     * @return array
     */
    protected function getSoapData($method, $show)
    {
        $methodHeader = $method.'Headers';
        $request = [
            'method' => $method,
            'header' => $this->soapClient->$methodHeader(),
            'body'   => $this->soapClient->$method()
        ];

        if ($show) {
            print_r($request);
        }

        return $request;
    }
}
