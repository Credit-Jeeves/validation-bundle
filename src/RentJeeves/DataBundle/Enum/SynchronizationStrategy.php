<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class SynchronizationStrategy extends Enum
{
    const REAL_TIME = 'real_time';

    const DEPOSITED = 'deposited';
}
