<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentBatchStatus extends Enum
{
    const OPENED = 'opened';

    const CLOSED = 'closed';
}
