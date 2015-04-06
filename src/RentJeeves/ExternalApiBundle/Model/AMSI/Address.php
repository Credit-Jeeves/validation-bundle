<?php

namespace RentJeeves\ExternalApiBundle\Model\AMSI;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("Address")
 */
class Address
{
    /**
     * @Serializer\SerializedName("AddressType")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $addressType;

    /**
     * @Serializer\SerializedName("AddressLine1")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $addressLine1;

    /**
     * @Serializer\SerializedName("AddressLine2")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $addressLine2;

    /**
     * @Serializer\SerializedName("City")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $city;

    /**
     * @Serializer\SerializedName("State")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $state;

    /**
     * @Serializer\SerializedName("ZipCode")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $zipCode;

    /**
     * @Serializer\SerializedName("Country")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $country;

    /**
     * @Serializer\SerializedName("Email")
     * @Serializer\XmlAttribute
     * @Serializer\Groups({"AMSI"})
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $email;

    /**
     * @Serializer\SerializedName("Contact")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Model\AMSI\Contact")
     * @Serializer\Groups({"AMSI"})
     *
     * @var Contact
     */
    protected $contact;

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contract
     */
    public function setContact(Contact $contract)
    {
        $this->contact = $contract;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine1
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine2
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
    }

    /**
     * @return string
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @param string $addressType
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }
}
