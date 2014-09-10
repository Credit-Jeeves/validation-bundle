<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

/**
 * This class is generated from the following WSDL:
 * ./src/RentJeeves/ExternalApiBundle/Resources/files/ItfResidentTransactions20.asmx.wsdl
 */
class PingResponse
{
    /**
     * PingResult
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: s:string
     *
     * @var string
     */
    protected $PingResult;

    /**
     * @param string $pingResult
     *
     * @return PingResponse
     */
    public function setPingResult($pingResult)
    {
        $this->PingResult = $pingResult;

        return $this;
    }

    /**
     * @return string
     */
    public function getPingResult()
    {
        return $this->PingResult;
    }
}
