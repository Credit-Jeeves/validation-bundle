<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class TransactionStatus extends Enum
{
    const COMPLETE = 'complete';

    const REVERSED = 'reversed';
}
