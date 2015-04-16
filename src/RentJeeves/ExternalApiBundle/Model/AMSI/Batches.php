<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Batches")
 */
class Batches
{
    /**
     * @Serializer\SerializedName("EDEX")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\AMSI\EdexSettlement")
     * @Serializer\XmlElement
     * @Serializer\Groups({"updateSettlementDataResponse"})
     *
     * @var EdexSettlement
     */
    protected $edex;

    /**
     * @return EdexSettlement
     */
    public function getEdex()
    {
        return $this->edex;
    }

    /**
     * @param EdexSettlement $edex
     */
    public function setEdex($edex)
    {
        $this->edex = $edex;
    }
}
