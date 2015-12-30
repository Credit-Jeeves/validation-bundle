<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentStatus extends Enum
{
    const ACTIVE = 'active';

    const CLOSE = 'close';

    const FLAGGED = 'flagged';
}
