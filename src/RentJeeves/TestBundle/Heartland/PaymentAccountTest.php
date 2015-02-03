<?php

namespace RentJeeves\TestBundle\Heartland;

use RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\PaymentAccount as Base;

class PaymentAccountTest extends Base
{
    public function getTokenResponse($tokenRequest, $merchantName)
    {
        switch ($merchantName) {
            case 'RentTrackCorp':
                return 'D5E0A348-A8E7-4B7B-AA3D-A75E90DFB9C2';
        }
        return "26232CDD-8A40-471E-91BA-608CDFD7EFB3";
        // FIXME need to improve because now it is magic!
    }
}
