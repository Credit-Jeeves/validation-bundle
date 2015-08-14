<?php
namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AccountResponseRecord
{
    /**
     * @var string
     */
    protected $recordType = 'A';

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
    protected $billingAccountId;

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
     * @Assert\Length(min = 1, max = 60)
     */
    protected $billingAccountNumber;

    /**
     * @var string
     * @Assert\Length(min = 1, max = 2)
     */
    protected $nsfReturnCount;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 45)
     */
    protected $nameOnBillingAccount;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 14)
     */
    protected $billingAccountNickname;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 64)
     */
    protected $address1;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 64)
     */
    protected $address2;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 32)
     */
    protected $city;

    /**
     * @var string
     *
     * @Assert\Length(min = 2, max = 2)
     */
    protected $state;

    /**
     * @var string
     *
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
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"Y", "N"})
     */
    protected $paperBillOnOffFlag = 'N';

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"Y", "N"})
     */
    protected $viewBillDetailFlag = 'N';

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 10)
     */
    protected $divisionId;

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
    public function getBillingAccountId()
    {
        return $this->billingAccountId;
    }

    /**
     * @param string $billingAccountId
     */
    public function setBillingAccountId($billingAccountId)
    {
        $this->billingAccountId = $billingAccountId;
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
    public function getBillingAccountNumber()
    {
        return $this->billingAccountNumber;
    }

    /**
     * @param string $billingAccountNumber
     */
    public function setBillingAccountNumber($billingAccountNumber)
    {
        $this->billingAccountNumber = $billingAccountNumber;
    }

    /**
     * @return string
     */
    public function getNsfReturnCount()
    {
        return $this->nsfReturnCount;
    }

    /**
     * @param string $nsfReturnCount
     */
    public function setNsfReturnCount($nsfReturnCount)
    {
        $this->nsfReturnCount = $nsfReturnCount;
    }

    /**
     * @return string
     */
    public function getNameOnBillingAccount()
    {
        return $this->nameOnBillingAccount;
    }

    /**
     * @param string $nameOnBillingAccount
     */
    public function setNameOnBillingAccount($nameOnBillingAccount)
    {
        $this->nameOnBillingAccount = $nameOnBillingAccount;
    }

    /**
     * @return string
     */
    public function getBillingAccountNickname()
    {
        return $this->billingAccountNickname;
    }

    /**
     * @param string $billingAccountNickname
     */
    public function setBillingAccountNickname($billingAccountNickname)
    {
        $this->billingAccountNickname = $billingAccountNickname;
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
    public function getPaperBillOnOffFlag()
    {
        return $this->paperBillOnOffFlag;
    }

    /**
     * @param string $paperBillOnOffFlag
     */
    public function setPaperBillOnOffFlag($paperBillOnOffFlag)
    {
        $this->paperBillOnOffFlag = $paperBillOnOffFlag;
    }

    /**
     * @return string
     */
    public function getViewBillDetailFlag()
    {
        return $this->viewBillDetailFlag;
    }

    /**
     * @param string $viewBillDetailFlag
     */
    public function setViewBillDetailFlag($viewBillDetailFlag)
    {
        $this->viewBillDetailFlag = $viewBillDetailFlag;
    }

    /**
     * @return string
     */
    public function getDivisionId()
    {
        return $this->divisionId;
    }

    /**
     * @param string $divisionId
     */
    public function setDivisionId($divisionId)
    {
        $this->divisionId = $divisionId;
    }
}
