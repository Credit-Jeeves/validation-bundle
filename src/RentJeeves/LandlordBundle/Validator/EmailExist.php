<?php

namespace RentJeeves\LandlordBundle\Validator;

use Symfony\Component\Validator\Constraint;

class EmailExist extends Constraint
{
    public $messageExist = 'contract.error.email.exist';

    public $messageExistParams = [];

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'user_email_exist';
    }
}
