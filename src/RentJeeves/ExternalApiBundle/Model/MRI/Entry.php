<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class Entry
{

    /**
     * @Serializer\SerializedName("Error")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\MRI\Error")
     * @Serializer\Groups({"MRI-Response"})
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