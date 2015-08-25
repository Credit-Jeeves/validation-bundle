<?php

namespace RentJeeves\ExternalApiBundle\Model\ResMan;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Customer")
 */
class Customer
{
    /**
     * It's residentId of User
     *
     * @Serializer\SerializedName("CustomerID")
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $customerId;

    /**
     * @Serializer\SerializedName("Type")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     * @Serializer\Groups({"ResMan"})
     */
    protected $type;

    /**
     * @Serializer\SerializedName("Property")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\ResMan\PropertyCustomer")
     * @Serializer\Groups({"ResMan"})
     * @Serializer\XmlElement(namespace="http://www.w3.org/2005/Atom")
     *
     * @var PropertyCustomer
     */
    protected $property;

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
     * @return PropertyCustomer
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param PropertyCustomer $property
     */
    public function setProperty(PropertyCustomer $property)
    {
        $this->property = $property;
    }

    /**
     * @param RtCustomer $rtCustomer
     * @return string
     */
    public function getExternalUnitId(RtCustomer $rtCustomer)
    {
        return sprintf(
            '%s|%s|%s',
            $this->getProperty()->getPrimaryId(),
            $rtCustomer->getRtUnit()->getUnit()->getInformation()->getBuildingID(),
            $rtCustomer->getRtUnit()->getUnitId()
        );
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

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
