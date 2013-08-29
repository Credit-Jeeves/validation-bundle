<?php

namespace RentJeeves\CoreBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InviteEmail extends Constraint
{
    public $message = 'error.email.already.use';

    public function validatedBy()
    {
        return 'invite_email_already_use';
    }
}
