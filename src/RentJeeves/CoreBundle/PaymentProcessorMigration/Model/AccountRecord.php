<?php
namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Model;

use JMS\Serializer\Annotation\AccessorOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AccessorOrder("custom", custom =
 *  {
 *      "recordType",
 *      "profileId",
 *      "billingAccountNumber",
 *      "divisionId",
 *      "nameOnBillingAccount",
 *      "billingAccountNickname",
 *      "address1",
 *      "address2",
 *      "city",
 *      "state",
 *      "zipCode",
 *      "countryCode",
 *      "paperBillOnOffFlag",
 *      "viewBillDetailFlag",
 *      "businessId",
 *  }
 * )
 */
class AccountRecord
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
     * @Assert\Length(min = 1, max = 60)
     */
    protected $billingAccountNumber;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 10)
     */
    protected $divisionId;

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
    protected $businessId;

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @return mixed
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @return string
     */
    public function getBillingAccountNumber()
    {
        return $this->billingAccountNumber;
    }

    /**
     * @return string
     */
    public function getDivisionId()
    {
        return $this->divisionId;
    }

    /**
     * @return string
     */
    public function getNameOnBillingAccount()
    {
        return $this->nameOnBillingAccount;
    }

    /**
     * @return string
     */
    public function getBillingAccountNickname()
    {
        return $this->billingAccountNickname;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getPaperBillOnOffFlag()
    {
        return $this->paperBillOnOffFlag;
    }

    /**
     * @return string
     */
    public function getViewBillDetailFlag()
    {
        return $this->viewBillDetailFlag;
    }

    /**
     * @return string
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * @param string $billingAccountNumber
     */
    public function setBillingAccountNumber($billingAccountNumber)
    {
        $this->billingAccountNumber = $billingAccountNumber;
    }

    /**
     * @param string $divisionId
     */
    public function setDivisionId($divisionId)
    {
        $this->divisionId = $divisionId;
    }

    /**
     * @param string $nameOnBillingAccount
     */
    public function setNameOnBillingAccount($nameOnBillingAccount)
    {
        $this->nameOnBillingAccount = $nameOnBillingAccount;
    }

    /**
     * @param string $billingAccountNickname
     */
    public function setBillingAccountNickname($billingAccountNickname)
    {
        $this->billingAccountNickname = $billingAccountNickname;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @param string $paperBillOnOffFlag
     */
    public function setPaperBillOnOffFlag($paperBillOnOffFlag)
    {
        $this->paperBillOnOffFlag = $paperBillOnOffFlag;
    }

    /**
     * @param string $viewBillDetailFlag
     */
    public function setViewBillDetailFlag($viewBillDetailFlag)
    {
        $this->viewBillDetailFlag = $viewBillDetailFlag;
    }

    /**
     * @param string $businessId
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
