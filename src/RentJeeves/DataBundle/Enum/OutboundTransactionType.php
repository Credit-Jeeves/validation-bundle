<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class OutboundTransactionType extends Enum
{
    const DEPOSIT = 'deposit';
    const REVERSAL = 'reversal';
}
