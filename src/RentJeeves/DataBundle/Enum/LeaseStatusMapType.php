<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class LeaseStatusMapType extends Enum
{
    //Lease created
    const NEW_ONE = 'new';
    //Lease updated
    const MATCH = 'match';
    //Do not update sytem with this lease
    const SKIP = 'no_email';
    //an error occurred. see error_messages
    const ERROR = 'error';

    /**
     * {@inheritdoc}
     */
    public static function isValid($value)
    {
        return is_null($value) || parent::isValid($value);
    }
}
