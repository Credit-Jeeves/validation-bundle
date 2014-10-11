<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class ResidentLeaseFiles
{
    /**
     * @Serializer\SerializedName("LeaseFile")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile")
     */
    protected $leaseFile;

    /**
     * @return mixed
     */
    public function getLeaseFile()
    {
        return $this->leaseFile;
    }

    /**
     * @param mixed $leaseFile
     */
    public function setLeaseFile($leaseFile)
    {
        $this->leaseFile = $leaseFile;
    }
}
