<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Exception;

use RentJeeves\DataBundle\Enum\PaymentGroundType;

class PaymentProcessorInvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param string $paymentProcessor
     * @return PaymentProcessorInvalidArgumentException
     */
    public static function invalidPaymentProcessor($paymentProcessor)
    {
        return new self(
            sprintf(
                'Invalid Payment Processor Type. It should be %s',
                $paymentProcessor
            )
        );
    }

    /**
     * @param string $paymentGroundType
     * @throws self
     */
    public static function assertPaymentGroundType($paymentGroundType)
    {
        if (!PaymentGroundType::isValid($paymentGroundType)) {
            throw new self(
                sprintf(
                    'Payment Ground Type "%s" is invalid',
                    $paymentGroundType
                )
            );
        }
    }
}
