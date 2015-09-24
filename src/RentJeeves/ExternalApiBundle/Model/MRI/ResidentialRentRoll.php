<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

/** @Serializer\XmlRoot("mri_s-pmrm_residentialrentroll") */
class ResidentialRentRoll
{
    /**
     * @Serializer\SerializedName("NextPageLink")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $nextPageLink = null;

    /**
     * @Serializer\SerializedName("entry")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\MRI\Value>")
     * @Serializer\Groups({"MRI-Response"})
     * @Serializer\XmlList(inline = true, entry = "entry")
     */
    protected $values;

    /**
     * @return string
     */
    public function getNextPageLink()
    {
        return $this->nextPageLink;
    }

    /**
     * @param string $nextPageLink
     */
    public function setNextPageLink($nextPageLink)
    {
        $this->nextPageLink = $nextPageLink;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }
}
