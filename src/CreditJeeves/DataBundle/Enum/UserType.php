<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class UserType extends Enum
{
    /**
     * @var string
     */
    const ADMIN = 'admin';

    /**
     * @var string
     */
    const APPLICANT = 'applicant';

    /**
     * @var string
     */
    const DEALER = 'dealer';

    /**
     * @var string
     */
    const TETNANT = 'tenant';

    /**
     * @var string
     */
    const LANDLORD = 'landlord';
}
