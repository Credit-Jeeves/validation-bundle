<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;

class Property
{
    /**
     * @Serializer\SerializedName("Code")
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * @Serializer\SerializedName("MarketingName")
     * @Serializer\Type("string")
     */
    protected $marketingName;

    /**
     * @Serializer\SerializedName("AddressLine1")
     * @Serializer\Type("string")
     */
    protected $addressLine1;

    /**
     * @Serializer\SerializedName("AddressLine2")
     * @Serializer\Type("string")
     */
    protected $addressLine2;

    /**
     * @Serializer\SerializedName("AddressLine3")
     * @Serializer\Type("string")
     */
    protected $addressLine3;

    /**
     * @Serializer\SerializedName("City")
     * @Serializer\Type("string")
     */
    protected $city;

    /**
     * @Serializer\SerializedName("State")
     * @Serializer\Type("string")
     */
    protected $state;

    /**
     * @Serializer\SerializedName("PostalCode")
     * @Serializer\Type("string")
     */
    protected $postalCode;

    /**
     * @Serializer\SerializedName("AccountsReceivable")
     * @Serializer\Type("string")
     */
    protected $accountsReceivable;

    /**
     * @Serializer\SerializedName("AccountsPayable")
     * @Serializer\Type("string")
     */
    protected $accountsPayable;

    /**
     * @param mixed $accountsPayable
     * @Serializer\Type("string")
     */
    public function setAccountsPayable($accountsPayable)
    {
        $this->accountsPayable = $accountsPayable;
    }

    /**
     * @return mixed
     */
    public function getAccountsPayable()
    {
        return $this->accountsPayable;
    }

    /**
     * @param mixed $accountsReceivable
     */
    public function setAccountsReceivable($accountsReceivable)
    {
        $this->accountsReceivable = $accountsReceivable;
    }

    /**
     * @return mixed
     */
    public function getAccountsReceivable()
    {
        return $this->accountsReceivable;
    }

    /**
     * @param mixed $addressLine1
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
    }

    /**
     * @return mixed
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param mixed $addressLine2
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param mixed $addressLine3
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;
    }

    /**
     * @return mixed
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $marketingName
     */
    public function setMarketingName($marketingName)
    {
        $this->marketingName = $marketingName;
    }

    /**
     * @return mixed
     */
    public function getMarketingName()
    {
        return $this->marketingName;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $unitName
     * @return string
     */
    public function getExternalUnitId($unitName)
    {
        return sprintf('%s||%s', $this->getCode(), $unitName);
    }
}
