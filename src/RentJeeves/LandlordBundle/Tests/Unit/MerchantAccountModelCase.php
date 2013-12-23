<?php

namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\LandlordBundle\Registration\MerchantAccountModel;

class MerchantAccountModelCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldSetRoutingNumberAccountNumberAccountTypeOnConstruct()
    {
        $routingNumber = '123456';
        $accountNumber = '987654';
        $accountType = 'AccountType';

        $merchantAccount = new MerchantAccountModel($routingNumber, $accountNumber, $accountType);

        $this->assertEquals($routingNumber, $merchantAccount->getRoutingNumber());
        $this->assertEquals($accountNumber, $merchantAccount->getAccountNumber());
        $this->assertEquals($accountType, $merchantAccount->getAccountType());
    }

    /**
     * @test
     */
    public function shouldAllowToSetNewRoutingNumber()
    {
        $merchantAccount = $this->getMerchantAccount();
        $merchantAccount->setRoutingNumber('555555');

        $this->assertEquals('555555', $merchantAccount->getRoutingNumber());
    }

    /**
     * @test
     */
    public function shouldAllowToSetNewAccountNumber()
    {
        $merchantAccount = $this->getMerchantAccount();
        $merchantAccount->setAccountNumber('111111');

        $this->assertEquals('111111', $merchantAccount->getAccountNumber());
    }

    /**
     * @test
     */
    public function shouldAllowToSetNewAccountType()
    {
        $merchantAccount = $this->getMerchantAccount();
        $merchantAccount->setAccountType('Savings');

        $this->assertEquals('Savings', $merchantAccount->getAccountType());
    }

    protected function getMerchantAccount()
    {
        $routingNumber = '123456';
        $accountNumber = '987654';
        $accountType = 'AccountType';

        return new MerchantAccountModel($routingNumber, $accountNumber, $accountType);
    }
}
