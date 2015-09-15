<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class Resident
{
    /**
     * @Serializer\SerializedName("ResidentID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $residentId;

    /**
     * @Serializer\SerializedName("ResidentStatus")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $residentStatus;

    /**
     * @return string
     */
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * @param string $residentId
     */
    public function setResidentId($residentId)
    {
        $this->residentId = $residentId;
    }

    /**
     * @return string
     */
    public function getResidentStatus()
    {
        return $this->residentStatus;
    }

    /**
     * @param string $residentStatus
     */
    public function setResidentStatus($residentStatus)
    {
        $this->residentStatus = $residentStatus;
    }
}
