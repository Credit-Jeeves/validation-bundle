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
    protected $lease;

    /**
     * @return array
     */
    public function getLease()
    {
        return $this->lease;
    }

    /**
     * @param array $lease
     */
    public function setLease($lease)
    {
        $this->lease = $lease;
    }
}
