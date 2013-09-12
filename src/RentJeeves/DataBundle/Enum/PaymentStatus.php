<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentStatus extends Enum
{
    const ACTIVE = 'active';
    const PAUSE = 'pause';
    const CLOSE = 'close';
}
