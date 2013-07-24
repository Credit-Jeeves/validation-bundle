<?php
namespace CreditJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class LoginCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
{
    /**
     * @test
     */
    public function userCanLogin()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->logout();
    }

    /**
     * @test
     * @depends userCanLogin
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function wrongPassword()
    {
        $this->login('admin@example.com', '123');
    }
}
