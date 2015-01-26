<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

class Customer
{
    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $type;

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

    /**
     * @return Lease
     */
    public function getLease()
    {
        return $this->lease;
    }

    /**
     * @param Lease $lease
     */
    public function setLease($lease)
    {
        $this->lease = $lease;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
