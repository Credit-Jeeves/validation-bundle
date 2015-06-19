<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class OutboundTransactionStatus extends Enum
{
    const SUCCESS = 'success';

    const CANCELLED = 'cancelled';

    const ERROR = 'error';
}
