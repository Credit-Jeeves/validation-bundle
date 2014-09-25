<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

/**
 * This class is generated from the following WSDL:
 * ./src/RentJeeves/ExternalApiBundle/Resources/files/ItfResidentTransactions20.asmx.wsdl
 */
class GetVersionNumberResponse
{
    /**
     * GetVersionNumberResult
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: s:string
     *
     * @var string
     */
    protected $GetVersionNumberResult;

    /**
     * @param string $getVersionNumberResult
     *
     * @return GetVersionNumberResponse
     */
    public function setGetVersionNumberResult($getVersionNumberResult)
    {
        $this->GetVersionNumberResult = $getVersionNumberResult;

        return $this;
    }

    /**
     * @return string
     */
    public function getGetVersionNumberResult()
    {
        return $this->GetVersionNumberResult;
    }
}
