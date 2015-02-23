<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Response
{
    /**
     * @Serializer\SerializedName("BatchID")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("string")
     */
    protected $batchId;

    /**
     * @Serializer\SerializedName("ResidentTransactions")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\ResidentTransactions")
     */
    protected $residentTransactions;

    /**
     * @return ResidentTransactions
     */
    public function getResidentTransactions()
    {
        return $this->residentTransactions;
    }

    /**
     * @param ResidentTransactions $residentTransactions
     */
    public function setResidentTransactions(ResidentTransactions $residentTransactions)
    {
        $this->residentTransactions = $residentTransactions;

        return $this;
    }

    public function getBatchId()
    {
        return $this->batchId;
    }

    public function setBatchId($batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }
}
