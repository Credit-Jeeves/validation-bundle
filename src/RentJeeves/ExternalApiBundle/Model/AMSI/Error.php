<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

class Error
{
    /**
     * @Serializer\SerializedName("ErrorCode")
     * @Serializer\Type("string")
     * @Serializer\Groups({"AMSI"})
     *
     * @var string
     */
    protected $errorCode;

    /**
     * @Serializer\SerializedName("ErrorDescription")
     * @Serializer\Type("string")
     * @Serializer\Groups({"AMSI"})
     *
     * @var string
     */
    protected $errorDescription;

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    /**
     * @param string $errorDescription
     */
    public function setErrorDescription($errorDescription)
    {
        $this->errorDescription = $errorDescription;
    }
}
