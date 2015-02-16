<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Response
{
    /**
     * @Serializer\SerializedName("BatchID")
     * @Serializer\Groups({"ResManOpenBatch"})
     * @Serializer\Type("string")
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
