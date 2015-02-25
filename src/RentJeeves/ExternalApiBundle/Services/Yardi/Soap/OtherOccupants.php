<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class OtherOccupants
{
    /**
     * @Serializer\SerializedName("OtherOccupant")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Services\Yardi\Soap\OtherOccupant>")
     * @Serializer\XmlList(inline = true, entry = "OtherOccupant")
     */
    protected $otherOccupant;

    /**
     * @return array
     */
    public function getOtherOccupants()
    {
        return $this->otherOccupant;
    }

    /**
     * @param array $otherOccupant
     */
    public function setOtherOccupants($otherOccupant)
    {
        $this->otherOccupant = $otherOccupant;
    }
}