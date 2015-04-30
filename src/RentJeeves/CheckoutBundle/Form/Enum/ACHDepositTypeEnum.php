<?php

namespace RentJeeves\CheckoutBundle\Form\Enum;

use CreditJeeves\CoreBundle\Enum;

class ACHDepositTypeEnum extends Enum
{
    const BUSINESS_CHECKING = 'Unassigned';

    const CHECKING = 'Checking';

    const SAVINGS = 'Savings';
}
