<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Group;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorInterface as PaymentProcessor;

/**
 * @DI\Service("payment_processor.factory")
 */
class PaymentProcessorFactory
{
    protected $heartland;

    /**
     * @DI\InjectParams({"heartland" = @DI\Inject("payment_processor.heartland")})
     */
    public function setPaymentProcessor(PaymentProcessor $heartland)
    {
        $this->heartland = $heartland;
    }

    /**
     * Returns a payment processor for a given payment.
     *
     * @param Group $group
     * @return PaymentProcessor
     */
    public function getPaymentProcessor(Group $group)
    {
        return $this->heartland;
    }
}
