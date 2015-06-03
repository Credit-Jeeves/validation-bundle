<?php
namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("PropertyResidents")
 */
class PropertyResidents
{
    /**
     * @Serializer\SerializedName("Lease")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\AMSI\Lease>")
     * @Serializer\XmlList(inline = true, entry = "Lease")
     * @Serializer\XmlKeyValuePairs
     * @Serializer\Groups({"AMSI"})
     *
     * @var array
     */
    protected $leases = [];

    /**
     * @return array
     */
    public function getLeases()
    {
        return $this->leases;
    }

    /**
     * @param array $lease
     */
    public function setLeases($lease)
    {
        $this->leases = $lease;
    }
}
