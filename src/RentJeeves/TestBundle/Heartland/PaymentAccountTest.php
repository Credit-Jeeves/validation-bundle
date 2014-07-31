<?php

namespace RentJeeves\TestBundle\Heartland;

use RentJeeves\CheckoutBundle\Payment\PaymentAccount as Base;

class PaymentAccountTest extends Base
{
    public function getTokenResponse($tokenRequest, $merchantName)
    {
        return "26232CDD-8A40-471E-91BA-608CDFD7EFB3";
    }
}
