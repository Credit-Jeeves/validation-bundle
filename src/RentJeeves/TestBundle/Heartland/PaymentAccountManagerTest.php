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
        return "26232CDD-8A40-471E-91BA-608CDFD7EFB3";
        // FIXME need to improve because now it is magic!
    }
}
