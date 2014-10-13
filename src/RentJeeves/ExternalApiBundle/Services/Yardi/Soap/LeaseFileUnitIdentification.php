<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class LeaseFileUnitIdentification 
{
    /**
     * @Serializer\SerializedName("IDValue")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $unitName;

    /**
     * @return mixed
     */
    public function getUnitName()
    {
        return $this->unitName;
    }

    /**
     * @param mixed $unitName
     */
    public function setUnitName($unitName)
    {
        $this->unitName = $unitName;
    }
} 
