<?php
namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Model;

use JMS\Serializer\Annotation\AccessorOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AccessorOrder("custom", custom =
 *  {
 *      "recordType",
 *      "profileId",
 *      "fundingAccountType",
 *      "bankAccountSubType",
 *      "fundingNickname",
 *      "routingNumber",
 *      "bankAccountNumber",
 *      "cardNumber",
 *      "cardExpirationMonth",
 *      "cardExpirationYear",
 *      "cardExpirationYear",
 *      "fundingAccountHolderName",
 *      "fundingAccountHolderAddress1",
 *      "fundingAccountHolderAddress2",
 *      "fundingAccountHolderCity",
 *      "fundingAccountHolderState",
 *      "fundingAccountHolderZipCode",
 *      "fundingAccountHolderCountryCode",
 *      "creditCardSubType",
 *      "businessId",
 *  }
 * )
 */
class FundingRecord
{
    /**
     * @var string
     */
    protected $recordType = 'F';

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 12)
     * @Assert\Regex("/^\d+/")
     */
    protected $profileId;

    /**
     * @var string
     */
    protected $fundingAccountType;

    /**
     * @var string
     */
    protected $bankAccountSubType;

    /**
     * @var string
     */
    protected $fundingNickname;

    /**
     * @var string
     */
    protected $routingNumber;

    /**
     * @var string
     */
    protected $bankAccountNumber;

    /**
     * @var string
     */
    protected $cardNumber;

    /**
     * @var string
     */
    protected $cardExpirationMonth;

    /**
     * @var string
     */
    protected $cardExpirationYear;

    /**
     * @var string
     */
    protected $fundingAccountHolderName;

    /**
     * @var string
     */
    protected $fundingAccountHolderAddress1;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Please set HPS Token here")
     */
    protected $fundingAccountHolderAddress2;

    /**
     * @var string
     */
    protected $fundingAccountHolderCity;

    /**
     * @var string
     */
    protected $fundingAccountHolderState;

    /**
     * @var string
     */
    protected $fundingAccountHolderZipCode;

    /**
     * @var string
     */
    protected $fundingAccountHolderCountryCode;

    /**
     * @var string
     */
    protected $creditCardSubType;

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
     * @return string
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * @return string
     */
    public function getFundingAccountType()
    {
        return $this->fundingAccountType;
    }

    /**
     * @return string
     */
    public function getBankAccountSubType()
    {
        return $this->bankAccountSubType;
    }

    /**
     * @return string
     */
    public function getFundingNickname()
    {
        return $this->fundingNickname;
    }

    /**
     * @return string
     */
    public function getRoutingNumber()
    {
        return $this->routingNumber;
    }

    /**
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @return string
     */
    public function getCardExpirationMonth()
    {
        return $this->cardExpirationMonth;
    }

    /**
     * @return string
     */
    public function getCardExpirationYear()
    {
        return $this->cardExpirationYear;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderName()
    {
        return $this->fundingAccountHolderName;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderAddress1()
    {
        return $this->fundingAccountHolderAddress1;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderAddress2()
    {
        return $this->fundingAccountHolderAddress2;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderCity()
    {
        return $this->fundingAccountHolderCity;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderState()
    {
        return $this->fundingAccountHolderState;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderZipCode()
    {
        return $this->fundingAccountHolderZipCode;
    }

    /**
     * @return string
     */
    public function getCreditCardSubType()
    {
        return $this->creditCardSubType;
    }

    /**
     * @return string
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * @param string $recordType
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * @param string $fundingAccountType
     */
    public function setFundingAccountType($fundingAccountType)
    {
        $this->fundingAccountType = $fundingAccountType;
    }

    /**
     * @param string $bankAccountSubType
     */
    public function setBankAccountSubType($bankAccountSubType)
    {
        $this->bankAccountSubType = $bankAccountSubType;
    }

    /**
     * @param string $fundingNickname
     */
    public function setFundingNickname($fundingNickname)
    {
        $this->fundingNickname = $fundingNickname;
    }

    /**
     * @param string $routingNumber
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;
    }

    /**
     * @param string $bankAccountNumber
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }

    /**
     * @param string $cardNumber
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    /**
     * @param string $cardExpirationMonth
     */
    public function setCardExpirationMonth($cardExpirationMonth)
    {
        $this->cardExpirationMonth = $cardExpirationMonth;
    }

    /**
     * @param string $cardExpirationYear
     */
    public function setCardExpirationYear($cardExpirationYear)
    {
        $this->cardExpirationYear = $cardExpirationYear;
    }

    /**
     * @param string $fundingAccountHolderName
     */
    public function setFundingAccountHolderName($fundingAccountHolderName)
    {
        $this->fundingAccountHolderName = $fundingAccountHolderName;
    }

    /**
     * @param string $fundingAccountHolderAddress1
     */
    public function setFundingAccountHolderAddress1($fundingAccountHolderAddress1)
    {
        $this->fundingAccountHolderAddress1 = $fundingAccountHolderAddress1;
    }

    /**
     * @param string $fundingAccountHolderAddress2
     */
    public function setFundingAccountHolderAddress2($fundingAccountHolderAddress2)
    {
        $this->fundingAccountHolderAddress2 = $fundingAccountHolderAddress2;
    }

    /**
     * @param string $fundingAccountHolderCity
     */
    public function setFundingAccountHolderCity($fundingAccountHolderCity)
    {
        $this->fundingAccountHolderCity = $fundingAccountHolderCity;
    }

    /**
     * @param string $fundingAccountHolderState
     */
    public function setFundingAccountHolderState($fundingAccountHolderState)
    {
        $this->fundingAccountHolderState = $fundingAccountHolderState;
    }

    /**
     * @param string $fundingAccountHolderZipCode
     */
    public function setFundingAccountHolderZipCode($fundingAccountHolderZipCode)
    {
        $this->fundingAccountHolderZipCode = $fundingAccountHolderZipCode;
    }

    /**
     * @param string $creditCardSubType
     */
    public function setCreditCardSubType($creditCardSubType)
    {
        $this->creditCardSubType = $creditCardSubType;
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
    public function getFundingAccountHolderCountryCode()
    {
        return $this->fundingAccountHolderCountryCode;
    }

    /**
     * @param string $fundingAccountHolderCountryCode
     */
    public function setFundingAccountHolderCountryCode($fundingAccountHolderCountryCode)
    {
        $this->fundingAccountHolderCountryCode = $fundingAccountHolderCountryCode;
    }
}
