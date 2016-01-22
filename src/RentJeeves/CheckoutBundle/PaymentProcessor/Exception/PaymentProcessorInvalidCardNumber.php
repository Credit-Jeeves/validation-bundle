<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Exception;

class PaymentProcessorInvalidCardNumber extends PaymentProcessorInvalidArgumentException
{
    /**
     * @param string $cardNumber
     * @return PaymentProcessorInvalidCardNumber
     */
    public static function invalidCardNumber($cardNumber)
    {
        return new self(
            sprintf(
                'Invalid Card Number "%s"',
                $cardNumber
            )
        );
    }
}
