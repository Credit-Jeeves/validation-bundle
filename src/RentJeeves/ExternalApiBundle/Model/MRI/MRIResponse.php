<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

/** @Serializer\XmlRoot("mri_s-pmrm_residentleasedetailsbypropertyid") */
class MRIResponse
{

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
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param string $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
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
    public function setValues($values)
    {
        $this->values = $values;
    }
}
