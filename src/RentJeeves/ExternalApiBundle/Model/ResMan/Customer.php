<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Customer
{
    /**
     * @Serializer\SerializedName("Name")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\UserName")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $userName;

    /**
     * @Serializer\SerializedName("Address")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Address")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $address;

    /**
     * @Serializer\SerializedName("Lease")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\Lease")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     */
    protected $lease;

    /**
     * @return UserName
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param UserName $name
     */
    public function setUserName($name)
    {
        $this->userName = $name;
    }
}
