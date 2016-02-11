<?php

namespace RentJeeves\TestBundle\Heartland;

use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentAccountManager as Base;

class PaymentAccountManagerTest extends Base
{
    public function getTokenResponse($tokenRequest, $merchantName)
    {
        switch ($merchantName) {
            case 'RentTrackCorp':
                return '568C0904-9174-46DE-BEC4-9B76599B28C5';
        }
        return "D98BB91F-952B-452C-A929-9FBEF5E1F0F7";
        // FIXME need to improve because now it is magic!
    }
}
