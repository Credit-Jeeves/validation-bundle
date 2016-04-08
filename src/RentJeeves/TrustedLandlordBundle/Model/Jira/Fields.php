<?php

namespace RentJeeves\TrustedLandlordBundle\Model\Jira;

use JMS\Serializer\Annotation as Serializer;

class Fields
{
    /**
     * @Serializer\SerializedName("customfield_10601")
     * @Serializer\Type("RentJeeves\TrustedLandlordBundle\Model\Jira\Type")
     * @var string
     */
    protected $type;

    /**
     * @Serializer\SerializedName("customfield_10602")
     * @Serializer\Type("string")
     * @var string
     */
    protected $companyName;

    /**
     * @Serializer\SerializedName("customfield_10603")
     * @Serializer\Type("string")
     * @var string
     */
    protected $firstName;

    /**
     * @Serializer\SerializedName("customfield_10604")
     * @Serializer\Type("string")
     * @var string
     */
    protected $lastName;

    /**
     * @Serializer\SerializedName("customfield_10605")
     * @Serializer\Type("string")
     * @var string
     */
    protected $phone;

    /**
     * @Serializer\SerializedName("customfield_10609")
     * @Serializer\Type("string")
     * @var string
     */
    protected $addressee;

    /**
     * @Serializer\SerializedName("customfield_10610")
     * @Serializer\Type("string")
     * @var string
     */
    protected $address1;

    /**
     * @Serializer\SerializedName("customfield_10611")
     * @Serializer\Type("string")
     * @var string
     */
    protected $address2;

    /**
     * @Serializer\SerializedName("customfield_10612")
     * @Serializer\Type("string")
     * @var string
     */
    protected $city;

    /**
     * @Serializer\SerializedName("customfield_10613")
     * @Serializer\Type("string")
     * @var string
     */
    protected $state;

    /**
     * @Serializer\SerializedName("customfield_10614")
     * @Serializer\Type("string")
     * @var string
     */
    protected $zip;

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getAddressee()
    {
        return $this->addressee;
    }

    /**
     * @param string $addressee
     */
    public function setAddressee($addressee)
    {
        $this->addressee = $addressee;
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
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

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
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     */
    public function setType(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
}
