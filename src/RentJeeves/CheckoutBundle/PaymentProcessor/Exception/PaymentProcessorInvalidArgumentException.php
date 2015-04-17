<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Exception;

class PaymentProcessorInvalidArgumentException extends \InvalidArgumentException
{
    public static function createInvalidPaymentProcessor($paymentProcessor)
    {
        return new self(
            sprintf(
                'Invalid Payment Processor Type. It should be %s',
                $paymentProcessor
            )
        );
    }
}
