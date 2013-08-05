<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class UserType extends Enum
{
    const APPLICANT = 'applicant';
    const ADMIN = 'admin';
    const DEALER = 'dealer';
    const TETNANT = 'tenant';
    const LANDLORD = 'landlord';
}
