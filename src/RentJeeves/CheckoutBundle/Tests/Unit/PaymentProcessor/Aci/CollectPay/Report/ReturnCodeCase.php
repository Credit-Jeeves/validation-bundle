<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay\Report;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\Report\ReturnCode;

class ReturnCodeCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideReturnCodes
     */
    public function shouldReturnCodeMessage($code, $expectedMessage)
    {
        $actualMessage = ReturnCode::getCodeMessage($code);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    /**
     * @return array
     */
    public function provideReturnCodes()
    {
        return [
            ['Q77', 'Non-matching account number'],
            ['Q818', 'Fraudulent transaction (Card present)'],
            ['Q818', 'Fraudulent transaction (Card present)'],
            ['4999', 'Domestic Chargeback Dispute (Europe Region Only)'],
            ['R01', 'Insufficient Funds'],
            ['R15', 'Beneficiary or Account Holder Deceased'],
            ['R555', ''], // Unknown code
            ['Q92', ''], // Unknown code
        ];
    }
}
