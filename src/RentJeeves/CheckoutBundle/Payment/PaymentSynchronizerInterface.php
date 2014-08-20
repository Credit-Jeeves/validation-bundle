<?php

namespace RentJeeves\CheckoutBundle\Payment;


interface PaymentSynchronizerInterface
{
    public function synchronize($makeArchive = false);
}
