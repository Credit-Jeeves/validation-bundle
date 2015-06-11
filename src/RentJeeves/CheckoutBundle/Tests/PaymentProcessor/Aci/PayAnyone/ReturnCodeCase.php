<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\ReturnCode;

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
            ['P01', 'Undeliverable Address'],
            ['OTC', 'Over the counter check biller return'],
            ['R02', 'Account Closed'],
            ['B21', 'Invalid biller identification number'],
            ['J19', 'Amount field error'],
            ['079', 'Biller does not accept or validate prenotes. Detail rejected'],
            ['R555', ''], // Unknown code
            ['X92', ''], // Unknown code
        ];
    }
}
