<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PropertyResidents")
 */
class PropertyUnits
{
    /**
     * @Serializer\SerializedName("Unit")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\AMSI\Unit>")
     * @Serializer\XmlList(inline = true, entry = "Unit")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"AMSI"})
     *
     * @var array
     */
    protected $units;

    /**
     * @return array
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @param array $lease
     */
    public function setUnits($lease)
    {
        $this->units = $lease;
    }
}
