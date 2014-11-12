<?php
namespace RentJeeves\ApiBundle\Validator;

use Symfony\Component\Validator\Constraint;

class ApiTenantEmail extends Constraint
{
    public $message = 'user.email.already.exist';

    public function validatedBy()
    {
        return 'api_tenant_email_validator';
    }
}
