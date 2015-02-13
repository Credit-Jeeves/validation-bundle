<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("BatchID")

 */
class Batch 
{
    /**
     * @Serializer\SerializedName("BatchID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("string")
     * @Serializer\XmlValue
     */
    protected $batchId;

    public function getBatchId()
    {
        return $this->batchId;
    }

    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;
    }
}
