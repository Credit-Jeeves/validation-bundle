<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("InternetTrafficResponse")
 */
class InternetTrafficResponse
{
    /**
     * @Serializer\SerializedName("Error")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\AMSI\Error")
     * @Serializer\Groups({"AMSI"})
     *
     * @var Error
     */
    protected $error;

    /**
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param Error $error
     */
    public function setError(Error $error)
    {
        $this->error = $error;
    }
}
