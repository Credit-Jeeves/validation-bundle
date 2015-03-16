<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class MRIResponse
{

    /**
     * @Serializer\SerializedName("odata.metadata")
     * @Serializer\Type("string")
     * @Serializer\Groups({"MRI-Response"})
     */
    protected $metadata;

    /**
     * @Serializer\SerializedName("value")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\MRI\Value>")
     * @Serializer\Groups({"MRI-Response"})
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
