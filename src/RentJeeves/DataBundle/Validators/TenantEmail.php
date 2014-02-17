<?php

namespace RentJeeves\DataBundle\Validators;

use Symfony\Component\Validator\Constraint;

class TenantEmail extends Constraint
{
    public $messageExistEmail = 'user.email.already.exist';

    public $messageGetInvite = 'tenant.already.invited';

    public function validatedBy()
    {
        return 'tenant_email_validator';
    }
}
