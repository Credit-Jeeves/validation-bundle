<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class DebitType extends Enum
{
    const DEBIT = 'debit';

    const SIGNATURE_NON_EXEMPT = 'signature_non_exempt';

    /**
     * {@inheritdoc}
     */
    public static function isValid($value)
    {
        return (is_null($value)) || parent::isValid($value);
    }
}
