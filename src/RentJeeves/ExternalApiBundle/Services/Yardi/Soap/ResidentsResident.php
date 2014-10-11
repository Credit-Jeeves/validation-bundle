<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentsResident
{
    /**
     * @Serializer\SerializedName("Status")
     * @Serializer\Type("string")
     */
    protected $status;

    /**
     * @Serializer\SerializedName("tCode")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}
