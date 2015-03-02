<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor;

use CreditJeeves\CoreBundle\Enum;

class PaymentProcessorReportType extends Enum
{
    const DEPOSIT = 'deposit';

    const REVERSALS = 'reversals';
}
