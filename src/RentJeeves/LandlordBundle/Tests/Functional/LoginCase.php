<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class LoginCase extends BaseTestCase
{
    /**
     * @test
     */
    public function userCanLogin()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->logout();
    }

    /**
     * @test
     * @depends userCanLogin
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function wrongPassword()
    {
        $this->login('landlord1@example.com', '123');
    }
}
