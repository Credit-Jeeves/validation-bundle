<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use RentJeeves\DataBundle\Validators\TenantEmail;
use RentJeeves\TestBundle\BaseTestCase;

class TenantEmailValidatorCase extends BaseTestCase
{
    /**
     * @test
     */
    public function index()
    {
        /**
         * @var $validator Validator
         */
        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validateValue(
            $email = 'tenant11@example.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 1));
    }

}
