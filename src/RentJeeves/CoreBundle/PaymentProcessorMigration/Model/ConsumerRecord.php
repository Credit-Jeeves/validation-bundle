<?php
namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Model;

use JMS\Serializer\Annotation\AccessorOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AccessorOrder("custom", custom =
 *  {
 *      "recordType",
 *      "profileId",
 *      "businessId",
 *      "userName",
 *      "password",
 *      "consumerFirstName",
 *      "consumerLastName",
 *      "primaryEmailAddress",
 *      "secondaryEmailAddress",
 *      "phoneNumber",
 *      "contactPhoneNumber",
 *      "textAddress",
 *      "address1",
 *      "address2",
 *      "city",
 *      "state",
 *      "zipCode",
 *      "countryCode"
 *  }
 * )
 */
class ConsumerRecord
{
    /**
     * @var string
     */
    protected $recordType = 'C';

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 12)
     * @Assert\Regex("/^\d+/")
     */
    protected $profileId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 10)
     * @Assert\Regex("/^\d+/")
     */
    protected $businessId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 32)
     * @Assert\Regex("/^\w+/")
     */
    protected $userName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 32)
     * @Assert\Regex("/^\w+/")
     */
    protected $password;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 45)
     */
    protected $consumerFirstName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 45)
     */
    protected $consumerLastName;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $primaryEmailAddress;

    /**
     * @var string
     *
     * @Assert\Email()
     */
    protected $secondaryEmailAddress;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 20)
     * @Assert\Regex("/^\d+/")
     */
    protected $phoneNumber;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 20)
     * @Assert\Regex("/^\d+/")
     */
    protected $contactPhoneNumber;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 256)
     */
    protected $textAddress;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 64)
     */
    protected $address1;

    /**
     * @var string
     */
    protected $address2 = '';

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 12)
     */
    protected $city;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 2, max = 2)
     */
    protected $state;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 9)
     */
    protected $zipCode;

    /**
     * @var string
     */
    protected $countryCode = 'US';

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @return string
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @return string
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getConsumerFirstName()
    {
        return $this->consumerFirstName;
    }

    /**
     * @return string
     */
    public function getConsumerLastName()
    {
        return $this->consumerLastName;
    }

    /**
     * @return string
     */
    public function getPrimaryEmailAddress()
    {
        return $this->primaryEmailAddress;
    }

    /**
     * @return string
     */
    public function getSecondaryEmailAddress()
    {
        return $this->secondaryEmailAddress;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return string
     */
    public function getContactPhoneNumber()
    {
        return $this->contactPhoneNumber;
    }

    /**
     * @return string
     */
    public function getTextAddress()
    {
        return $this->textAddress;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * @param string $businessId
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $consumerFirstName
     */
    public function setConsumerFirstName($consumerFirstName)
    {
        $this->consumerFirstName = $consumerFirstName;
    }

    /**
     * @param string $consumerLastName
     */
    public function setConsumerLastName($consumerLastName)
    {
        $this->consumerLastName = $consumerLastName;
    }

    /**
     * @param string $primaryEmailAddress
     */
    public function setPrimaryEmailAddress($primaryEmailAddress)
    {
        $this->primaryEmailAddress = $primaryEmailAddress;
    }

    /**
     * @param string $secondaryEmailAddress
     */
    public function setSecondaryEmailAddress($secondaryEmailAddress)
    {
        $this->secondaryEmailAddress = $secondaryEmailAddress;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @param string $contactPhoneNumber
     */
    public function setContactPhoneNumber($contactPhoneNumber)
    {
        $this->contactPhoneNumber = $contactPhoneNumber;
    }

    /**
     * @param string $textAddress
     */
    public function setTextAddress($textAddress)
    {
        $this->textAddress = $textAddress;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }
}
