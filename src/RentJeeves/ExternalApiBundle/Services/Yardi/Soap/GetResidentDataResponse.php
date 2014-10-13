<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("MITS-ResidentData")
 */
class GetResidentDataResponse 
{
    /**
     * @Serializer\SerializedName("LeaseFiles")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFiles")
     */
    protected $leaseFiles;

    /**
     * @return mixed
     */
    public function getLeaseFiles()
    {
        return $this->leaseFiles;
    }

    /**
     * @param mixed $leaseFiles
     */
    public function setLeaseFiles($leaseFiles)
    {
        $this->leaseFiles = $leaseFiles;
    }
}
