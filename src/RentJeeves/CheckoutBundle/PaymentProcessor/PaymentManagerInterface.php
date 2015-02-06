<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use RentJeeves\DataBundle\Enum\PaymentGroundType;

interface PaymentManagerInterface
{
    public function executePayment(
        Order $order,
        PaymentAccount $paymentAccount,
        $paymentType = PaymentGroundType::RENT
    );
}
