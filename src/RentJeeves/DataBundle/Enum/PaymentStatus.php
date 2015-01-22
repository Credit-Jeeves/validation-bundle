<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentStatus extends Enum
{
    /**
     * @var string
     */
    const ACTIVE = 'active';

    /**
     * @var string
     */
    const CLOSE = 'close';
}
