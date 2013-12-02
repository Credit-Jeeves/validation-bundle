<?php

namespace RentJeeves\LandlordBundle\Registration;


class MerchantAccountModel 
{
    protected $routingNumber;
    protected $accountNumber;
    protected $accountType;

    public function __construct($routingNumber, $accountNumber, $accountType)
    {
        $this->setRoutingNumber($routingNumber);
        $this->setAccountNumber($accountNumber);
        $this->setAccountType($accountType);
    }

    /**
     * @param int $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return int
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param int $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * @return int
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param int $routingNumber
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;
    }

    /**
     * @return int
     */
    public function getRoutingNumber()
    {
        return $this->routingNumber;
    }
}
