<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportLeaseUserStatus extends Enum
{
    /**
     * Email invite was sent successfully
     */
    const INVITED = 'invited';

    /**
     * When group settings disables inviting
     */
    const NOT_INVITED = 'not_invited';

    /**
     * Lease created but no invite send due to missing email address
     */
    const NO_EMAIL = 'no_email';

    /**
     * lease created but no invite send due to bad or blacklisted email address
     */
    const BAD_EMAIL = 'bad_email';

    /**
     * an error occurred. see error_messages
     */
    const ERROR = 'error';
}
