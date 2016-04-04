<?php

namespace RentJeeves\TrustedLandlordBundle\Model;

use RentJeeves\DataBundle\Enum\TrustedLandlordType;
use Symfony\Component\Validator\Constraints as Assert;

class TrustedLandlordDTO
{
    /**
     * @Assert\NotBlank(groups={"landlord", "person"}, message="email.required")
     * @var string
     */
    protected $email;

    /**
     * @Assert\Choice(
     *     callback={"RentJeeves\DataBundle\Enum\TrustedLandlordType", "all"},
     *     groups={"trusted_landlord"}
     * )
     * @var string
     */
    protected $type = TrustedLandlordType::PERSON;

    /**
     * @Assert\NotBlank(groups={"person"})
     * @var string
     */
    protected $firstName;

    /**
     * @Assert\NotBlank(groups={"person"})
     * @var string
     */
    protected $lastName;

    /**
     * @Assert\NotBlank(groups={"company"})
     * @var string
     */
    protected $companyName;

    /** @var string */
    protected $phone;

    /**
     * @Assert\NotBlank(groups={"trusted_landlord"})
     * @var string
     */
    protected $addressee;

    /**
     * @Assert\NotBlank(groups={"trusted_landlord"})
     * @var string
     */
    protected $address1;

    /**
     * @Assert\NotBlank(groups={"trusted_landlord"})
     * @var string
     */
    protected $address2;

    /**
     * @Assert\NotBlank(groups={"trusted_landlord"})
     * @var string
     */
    protected $state;

    /**
     * @Assert\NotBlank(groups={"trusted_landlord"})
     * @var string
     */
    protected $city;

    /**
     * @Assert\NotBlank(groups={"trusted_landlord"})
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
