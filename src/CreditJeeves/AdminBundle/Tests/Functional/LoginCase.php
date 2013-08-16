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

    /**
     * @test
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function passwordDefense()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->logout();
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', '123');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($error = $this->page->findAll('css', 'div.login-error'));
        $this->assertEquals('Please', $error->getText());
    }
}
