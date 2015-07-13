<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay;

use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\AbstractManager;

class AbstractManagerCase extends \PHPUnit_Framework_TestCase
{
    public function providerForRemoveDebugInformation()
    {
        return [
            [
                '[fundingAccount][account].cartNumber: Card Number is invalid',
                'Card Number is invalid'
            ],
            [
                '[fundingAccount][account].cartNumber: 888: Card Number is invalid',
                'Card Number is invalid'
            ],
            [
                '[fundingAccount][account].cartNumber: No: Card Number is invalid',
                'No: Card Number is invalid'
            ],
            [
                '[fundingAccount][account].cartNumber: 888: Hey: Card Number is invalid',
                'Hey: Card Number is invalid'
            ],
            [
                '[fundingAccount][account].cartNumber: Hoo: Hey: Card Number is invalid',
                'Hoo: Hey: Card Number is invalid'
            ],
            [
                '[fundingAccount][account].cartNumber: 888 : Hoo: Hey: Card Number is invalid',
                'Hoo: Hey: Card Number is invalid'
            ],
            [
                'Card Number is invalid',
                'Card Number is invalid'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider providerForRemoveDebugInformation
     */
    public function shouldCheckRemoveDebugInformation($currentMessage, $expectedMessage)
    {
        $resultMessage = AbstractManager::removeDebugInformation($currentMessage);
        $this->assertEquals($expectedMessage, $resultMessage);
    }
}
