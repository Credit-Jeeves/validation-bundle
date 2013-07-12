<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class LoginCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
{
    /**
     * @test
     */
    public function userCanLogin()
    {
        $this->setDefaultSession('symfony');
        $this->load(true);
        $this->login('emilio@example.com', 'pass');
        $this->logout();
    }

    /**
     * @test
     * @depends userCanLogin
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function wrongPassword()
    {
        $this->login('emilio@example.com', '123');
    }
}
