<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use RentJeeves\DataBundle\Validators\TenantEmail;
use RentJeeves\DataBundle\Validators\TenantEmailValidator;
use RentJeeves\TestBundle\BaseTestCase;
use Symfony\Component\HttpFoundation\Session\Session;

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
        /**
         * @var $session Session
         */
        $session = $this->getContainer()->get('session');
        $errors = $session->getFlashBag()->get(TenantEmailValidator::ERROR_NAME);
        $this->assertTrue(('user.email.already.exist' === $errors[0]));

        $errors = $validator->validateValue(
            $email = 'john@rentrack.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 1));
        /**
         * @var $session Session
         */
        $session = $this->getContainer()->get('session');
        $errors = $session->getFlashBag()->get(TenantEmailValidator::ERROR_NAME);
        $this->assertTrue(('tenant.already.invited' === $errors[0]));

        $errors = $validator->validateValue(
            $email = 'landlord1@example.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 1));
        /**
         * @var $session Session
         */
        $session = $this->getContainer()->get('session');
        $errors = $session->getFlashBag()->get(TenantEmailValidator::ERROR_NAME);
        $this->assertTrue(('user.email.already.exist' === $errors[0]));

        $errors = $validator->validateValue(
            $email = 'not-exist@example.com',
            new TenantEmail()
        );
        $this->assertTrue((count($errors) === 0));
    }

}
