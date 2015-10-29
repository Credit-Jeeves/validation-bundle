<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class BankAccountType extends Enum
{
    const CHECKING = 'checking';

    const SAVINGS = 'savings';

    const BUSINESS_CHECKING = 'business checking';

    /**
     * {@inheritdoc}
     */
    public static function isValid($value)
    {
        return (is_null($value)) || parent::isValid($value);
    }
}
