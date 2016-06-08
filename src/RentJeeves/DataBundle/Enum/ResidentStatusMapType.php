<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ResidentStatusMapType extends Enum
{
    //email invite was sent successfully
    const INVITED = 'invited';
    //when group settings disables inviting
    const NOT_INVITED = 'not_invited';
    //lease created but no invite send due to missing email address
    const NO_EMAIL = 'no_email';
    //lease created but no invite send due to bad or blacklisted email address
    const BAD_EMAIL = 'bad_email';
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
