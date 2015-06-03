<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class BankAccountType extends Enum
{
    const CHECKING = 'checking';

    const SAVINGS = 'savings';

    const BUSINESS_CHECKING = 'business checking';

    const DEFAULT_VALUE = null;
}
