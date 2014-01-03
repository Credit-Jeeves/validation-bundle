<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Error")
 */
class Error
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ErrorDescription")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $errorDescription;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ErrorCode")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $errorCode;

    /**
     * @param integer $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = (int) $errorCode;
    }

    /**
     * @return integer
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorDescription
     */
    public function setErrorDescription($errorDescription)
    {
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }
}
