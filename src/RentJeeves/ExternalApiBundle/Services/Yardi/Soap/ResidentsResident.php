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
     * @Serializer\SerializedName("MoveOutDate")
     * @Serializer\Type("string")
     */
    protected $moveOutDate;

    /**
     * @return mixed
     */
    public function getMoveOutDate()
    {
        return $this->moveOutDate;
    }

    /**
     * @param mixed $moveOutDate
     */
    public function setMoveOutDate($moveOutDate)
    {
        $this->moveOutDate = $moveOutDate;
    }

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
