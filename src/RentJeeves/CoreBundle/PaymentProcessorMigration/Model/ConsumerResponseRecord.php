<?php
namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ConsumerResponseRecord
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
     * @Assert\Length(min = 1, max = 12)
     */
    protected $consumerProfileId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 10)
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
     */
    protected $challengeQuestion1;

    /**
     * @var string
     */
    protected $challengeAnswer1;

    /**
     * @var string
     */
    protected $challengeQuestion2;

    /**
     * @var string
     */
    protected $challengeAnswer2;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 64)
     */
    protected $address1;

    /**
     * @var string
     * @Assert\Length(min = 1, max = 64)
     */
    protected $address2;

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
     * @Assert\Choice(choices = {"R", "E"})
     */
    protected $status;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 64)
     */
    protected $rejectReason;

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @param string $recordType
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;
    }

    /**
     * @return string
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * @return string
     */
    public function getConsumerProfileId()
    {
        return $this->consumerProfileId;
    }

    /**
     * @param string $consumerProfileId
     */
    public function setConsumerProfileId($consumerProfileId)
    {
        $this->consumerProfileId = $consumerProfileId;
    }

    /**
     * @return string
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * @param string $businessId
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getConsumerFirstName()
    {
        return $this->consumerFirstName;
    }

    /**
     * @param string $consumerFirstName
     */
    public function setConsumerFirstName($consumerFirstName)
    {
        $this->consumerFirstName = $consumerFirstName;
    }

    /**
     * @return string
     */
    public function getConsumerLastName()
    {
        return $this->consumerLastName;
    }

    /**
     * @param string $consumerLastName
     */
    public function setConsumerLastName($consumerLastName)
    {
        $this->consumerLastName = $consumerLastName;
    }

    /**
     * @return string
     */
    public function getPrimaryEmailAddress()
    {
        return $this->primaryEmailAddress;
    }

    /**
     * @param string $primaryEmailAddress
     */
    public function setPrimaryEmailAddress($primaryEmailAddress)
    {
        $this->primaryEmailAddress = $primaryEmailAddress;
    }

    /**
     * @return string
     */
    public function getSecondaryEmailAddress()
    {
        return $this->secondaryEmailAddress;
    }

    /**
     * @param string $secondaryEmailAddress
     */
    public function setSecondaryEmailAddress($secondaryEmailAddress)
    {
        $this->secondaryEmailAddress = $secondaryEmailAddress;
    }

    /**
     * @return string
     */
    public function getChallengeQuestion1()
    {
        return $this->challengeQuestion1;
    }

    /**
     * @param string $challengeQuestion1
     */
    public function setChallengeQuestion1($challengeQuestion1)
    {
        $this->challengeQuestion1 = $challengeQuestion1;
    }

    /**
     * @return string
     */
    public function getChallengeAnswer1()
    {
        return $this->challengeAnswer1;
    }

    /**
     * @param string $challengeAnswer1
     */
    public function setChallengeAnswer1($challengeAnswer1)
    {
        $this->challengeAnswer1 = $challengeAnswer1;
    }

    /**
     * @return string
     */
    public function getChallengeQuestion2()
    {
        return $this->challengeQuestion2;
    }

    /**
     * @param string $challengeQuestion2
     */
    public function setChallengeQuestion2($challengeQuestion2)
    {
        $this->challengeQuestion2 = $challengeQuestion2;
    }

    /**
     * @return string
     */
    public function getChallengeAnswer2()
    {
        return $this->challengeAnswer2;
    }

    /**
     * @param string $challengeAnswer2
     */
    public function setChallengeAnswer2($challengeAnswer2)
    {
        $this->challengeAnswer2 = $challengeAnswer2;
    }

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

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getContactPhoneNumber()
    {
        return $this->contactPhoneNumber;
    }

    /**
     * @param string $contactPhoneNumber
     */
    public function setContactPhoneNumber($contactPhoneNumber)
    {
        $this->contactPhoneNumber = $contactPhoneNumber;
    }

    /**
     * @return string
     */
    public function getTextAddress()
    {
        return $this->textAddress;
    }

    /**
     * @param string $textAddress
     */
    public function setTextAddress($textAddress)
    {
        $this->textAddress = $textAddress;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getRejectReason()
    {
        return $this->rejectReason;
    }

    /**
     * @param string $rejectReason
     */
    public function setRejectReason($rejectReason)
    {
        $this->rejectReason = $rejectReason;
    }
}
