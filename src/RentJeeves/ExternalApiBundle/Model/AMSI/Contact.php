<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Contact")
 */
class Contact
{
    /**
     * @Serializer\SerializedName("Phone")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $phone;

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}
