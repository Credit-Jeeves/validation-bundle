<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;
use CreditJeeves\DataBundle\Enum\OrderType;

class PaymentProcessor extends Enum
{
    const HEARTLAND = 'heartland';

    const ACI = 'aci';
}
