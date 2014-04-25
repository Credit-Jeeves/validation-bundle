<?php

namespace RentJeeves\DataBundle\Validators;

/**
 * This validator add error into flash with key landlord_email_error
 *
 * Class TenantEmail
 * @package RentJeeves\DataBundle\Validators
 */
class LandlordEmail extends TenantEmail
{
    public function validatedBy()
    {
        return 'landlord_email_validator';
    }
}
