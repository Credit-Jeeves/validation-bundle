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
        $this->load(true);
        self::$kernel = null;
        /**
         * @var $validator Validator
         */
        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validateValue(
            $email = 'tenant11@example.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 1));
        $error = $errors[0]->getMessage();
        $this->assertTrue(('user.email.already.exist' === $error));

        $errors = $validator->validateValue(
            $email = 'john@rentrack.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 1));
        $error = $errors[0]->getMessage();
        $this->assertTrue(('tenant.already.invited' === $error));

        $errors = $validator->validateValue(
            $email = 'landlord1@example.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 1));
        $error = $errors[0]->getMessage();
        $this->assertTrue(('user.email.already.exist' === $error));
        
        $errors = $validator->validateValue(
            $email = 'not-exist@example.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 0));
    }

}
