<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class CurrentCharges
{
    /**
     * @Serializer\SerializedName("entry")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\MRI\Charge>")
     * @Serializer\Groups({"MRI-Response"})
     * @Serializer\XmlList(inline = true, entry = "entry")
     *
     * @var array
     */
    protected $charges;

    /**
     * @return Charge
     */
    public function getCharges()
    {
        return $this->charges;
    }

    /**
     * @param array $charge
     */
    public function setCharge(array $charge)
    {
        $this->charge = $charge;
    }
}
