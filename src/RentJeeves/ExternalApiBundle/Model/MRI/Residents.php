<?php

namespace RentJeeves\ExternalApiBundle\Model\MRI;

use JMS\Serializer\Annotation as Serializer;

class Residents
{
    /**
     * @Serializer\SerializedName("entry")
     * @Serializer\Type("array<RentJeeves\ExternalApiBundle\Model\MRI\Resident>")
     * @Serializer\Groups({"MRI-Response"})
     * @Serializer\XmlList(inline = true, entry = "entry")
     *
     * @var array
     */
    protected $residentArray;

    /**
     * @return array
     */
    public function getResidentArray()
    {
        return $this->residentArray;
    }

    /**
     * @param array $residentArray
     */
    public function setResidentArray($residentArray)
    {
        $this->residentArray = $residentArray;
    }

}
