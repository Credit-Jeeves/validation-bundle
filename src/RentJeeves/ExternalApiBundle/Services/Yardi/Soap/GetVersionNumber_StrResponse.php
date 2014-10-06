<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

/**
 * This class is generated from the following WSDL:
 * ./src/RentJeeves/ExternalApiBundle/Resources/files/ItfResidentTransactions20.asmx.wsdl
 */
class GetVersionNumber_StrResponse
{
    /**
     * GetVersionNumber_StrResult
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: s:string
     *
     * @var string
     */
    protected $GetVersionNumber_StrResult;

    /**
     * @param string $getVersionNumber_StrResult
     *
     * @return GetVersionNumber_StrResponse
     */
    public function setGetVersionNumber_StrResult($getVersionNumber_StrResult)
    {
        $this->GetVersionNumber_StrResult = $getVersionNumber_StrResult;

        return $this;
    }

    /**
     * @return string
     */
    public function getGetVersionNumber_StrResult()
    {
        return $this->GetVersionNumber_StrResult;
    }
}
