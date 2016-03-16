<?php
namespace RentJeeves\CoreBundle\PaymentProcessorMigration\Model;

use Symfony\Component\Validator\Constraints as Assert;

class FundingResponseRecord
{
    /**
     * @var string
     */
    protected $recordType = 'F';

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
     * @Assert\Length(min = 1, max = 32)
     */
    protected $filer1;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 12)
     */
    protected $fundingAccountId;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 14)
     */
    protected $fundingAccountNickname;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"BANK", "CREDIT CARD", "DEBIT CARD"})
     */
    protected $fundingAccountType;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 32)
     */
    protected $filer2;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 1, max = 45)
     */
    protected $fundingAccountHolderName;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 64)
     */
    protected $fundingAccountHolderAddress1;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 64)
     */
    protected $fundingAccountHolderAddress2;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 32)
     */
    protected $fundingAccountHolderCity;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 32)
     */
    protected $fundingAccountHolderState;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 9)
     */
    protected $fundingAccountHolderZipCode;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 2)
     */
    protected $fundingAccountHolderCountryCode;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 20)
     */
    protected $fundingAccountPhoneNumber;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 9)
     */
    protected $routingNumber;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 17)
     */
    protected $bankAccountNumber;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 20)
     */
    protected $bankAccountSubType;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 2)
     */
    protected $cardExpirationMonth;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 20)
     */
    protected $creditCardSubType;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 4)
     */
    protected $cardExpirationYear;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 19)
     */
    protected $cardNumber;

    /**
     * @var string
     *
     * @Assert\Length(min = 1, max = 32)
     */
    protected $cardRoute;

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
    public function getFiler1()
    {
        return $this->filer1;
    }

    /**
     * @param string $filer1
     */
    public function setFiler1($filer1)
    {
        $this->filer1 = $filer1;
    }

    /**
     * @return mixed
     */
    public function getFundingAccountId()
    {
        return $this->fundingAccountId;
    }

    /**
     * @param mixed $fundingAccountId
     */
    public function setFundingAccountId($fundingAccountId)
    {
        $this->fundingAccountId = $fundingAccountId;
    }

    /**
     * @return string
     */
    public function getFundingAccountNickname()
    {
        return $this->fundingAccountNickname;
    }

    /**
     * @param string $fundingAccountNickname
     */
    public function setFundingAccountNickname($fundingAccountNickname)
    {
        $this->fundingAccountNickname = $fundingAccountNickname;
    }

    /**
     * @return string
     */
    public function getFundingAccountType()
    {
        return $this->fundingAccountType;
    }

    /**
     * @param string $fundingAccountType
     */
    public function setFundingAccountType($fundingAccountType)
    {
        $this->fundingAccountType = $fundingAccountType;
    }

    /**
     * @return string
     */
    public function getFiler2()
    {
        return $this->filer2;
    }

    /**
     * @param string $filer2
     */
    public function setFiler2($filer2)
    {
        $this->filer2 = $filer2;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderName()
    {
        return $this->fundingAccountHolderName;
    }

    /**
     * @param string $fundingAccountHolderName
     */
    public function setFundingAccountHolderName($fundingAccountHolderName)
    {
        $this->fundingAccountHolderName = $fundingAccountHolderName;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderAddress1()
    {
        return $this->fundingAccountHolderAddress1;
    }

    /**
     * @param string $fundingAccountHolderAddress1
     */
    public function setFundingAccountHolderAddress1($fundingAccountHolderAddress1)
    {
        $this->fundingAccountHolderAddress1 = $fundingAccountHolderAddress1;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderAddress2()
    {
        return $this->fundingAccountHolderAddress2;
    }

    /**
     * @param string $fundingAccountHolderAddress2
     */
    public function setFundingAccountHolderAddress2($fundingAccountHolderAddress2)
    {
        $this->fundingAccountHolderAddress2 = $fundingAccountHolderAddress2;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderCity()
    {
        return $this->fundingAccountHolderCity;
    }

    /**
     * @param string $fundingAccountHolderCity
     */
    public function setFundingAccountHolderCity($fundingAccountHolderCity)
    {
        $this->fundingAccountHolderCity = $fundingAccountHolderCity;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderState()
    {
        return $this->fundingAccountHolderState;
    }

    /**
     * @param string $fundingAccountHolderState
     */
    public function setFundingAccountHolderState($fundingAccountHolderState)
    {
        $this->fundingAccountHolderState = $fundingAccountHolderState;
    }

    /**
     * @return string
     */
    public function getFundingAccountHolderZipCode()
    {
        return $this->fundingAccountHolderZipCode;
    }

    /**
     * @param string $fundingAccountHolderZipCode
     */
    public function setFundingAccountHolderZipCode($fundingAccountHolderZipCode)
    {
        $this->fundingAccountHolderZipCode = $fundingAccountHolderZipCode;
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

    /**
     * @return string
     */
    public function getFundingAccountPhoneNumber()
    {
        return $this->fundingAccountPhoneNumber;
    }

    /**
     * @param string $fundingAccountPhoneNumber
     */
    public function setFundingAccountPhoneNumber($fundingAccountPhoneNumber)
    {
        $this->fundingAccountPhoneNumber = $fundingAccountPhoneNumber;
    }

    /**
     * @return string
     */
    public function getRoutingNumber()
    {
        return $this->routingNumber;
    }

    /**
     * @param string $routingNumber
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;
    }

    /**
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * @param string $bankAccountNumber
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }

    /**
     * @return string
     */
    public function getBankAccountSubType()
    {
        return $this->bankAccountSubType;
    }

    /**
     * @param string $bankAccountSubType
     */
    public function setBankAccountSubType($bankAccountSubType)
    {
        $this->bankAccountSubType = $bankAccountSubType;
    }

    /**
     * @return string
     */
    public function getCardExpirationMonth()
    {
        return $this->cardExpirationMonth;
    }

    /**
     * @param string $cardExpirationMonth
     */
    public function setCardExpirationMonth($cardExpirationMonth)
    {
        $this->cardExpirationMonth = $cardExpirationMonth;
    }

    /**
     * @return string
     */
    public function getCreditCardSubType()
    {
        return $this->creditCardSubType;
    }

    /**
     * @param string $creditCardSubType
     */
    public function setCreditCardSubType($creditCardSubType)
    {
        $this->creditCardSubType = $creditCardSubType;
    }

    /**
     * @return string
     */
    public function getCardExpirationYear()
    {
        return $this->cardExpirationYear;
    }

    /**
     * @param string $cardExpirationYear
     */
    public function setCardExpirationYear($cardExpirationYear)
    {
        $this->cardExpirationYear = $cardExpirationYear;
    }

    /**
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    /**
     * @return string
     */
    public function getCardRoute()
    {
        return $this->cardRoute;
    }

    /**
     * @param string $cardRoute
     */
    public function setCardRoute($cardRoute)
    {
        $this->cardRoute = $cardRoute;
    }
}
