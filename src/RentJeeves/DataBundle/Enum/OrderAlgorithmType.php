<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class OrderAlgorithmType extends Enum
{
    const SUBMERCHANT = 'submerchant';
    const PAYDIRECT = 'pay_direct';
}
