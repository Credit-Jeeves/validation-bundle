<?php

namespace RentJeeves\DataBundle\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * This validator add error into flash with key tenant_email_error
 *
 * Class TenantEmail
 * @package RentJeeves\DataBundle\Validators
 */
class TenantEmail extends Constraint
{
    public $messageExistEmail = 'user.email.already.exist';

    public $messageGetInvite = 'tenant.already.invited';

    public function validatedBy()
    {
        return 'tenant_email_validator';
    }
}
