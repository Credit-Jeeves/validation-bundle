<?php

namespace RentJeeves\TestBundle\ProfitStars\Mocks;

use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\RegisterCustomerResponse;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\ReturnValue;
use RentTrack\ProfitStarsClientBundle\PaymentVault\Model\WSUpdateResult;

class PaymentVaultClientMock
{
    /**
     * @param string $returnValue
     * @param int $invokeTimes
     * @return \RentTrack\ProfitStarsClientBundle\PaymentVault\Model\PaymentVaultClient
     */
    public static function getMockForRegisterCustomer($returnValue = ReturnValue::SUCCESS, $invokeTimes = 1)
    {
        $mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
        $mock = $mockGenerator->getMock(
            '\RentTrack\ProfitStarsClientBundle\PaymentVault\Model\PaymentVaultClient',
            [],
            [],
            '',
            false
        );
        $clientResponse = new RegisterCustomerResponse();
        $registerCustomerResult = new WSUpdateResult();
        $registerCustomerResult->setReturnValue($returnValue);
        $clientResponse->setRegisterCustomerResult($registerCustomerResult);

        $mock
            ->expects(new \PHPUnit_Framework_MockObject_Matcher_InvokedCount($invokeTimes))
            ->method('RegisterCustomer')
            ->will(new \PHPUnit_Framework_MockObject_Stub_Return($clientResponse));

        return $mock;
    }
}
