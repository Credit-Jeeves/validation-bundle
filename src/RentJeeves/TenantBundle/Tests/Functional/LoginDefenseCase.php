<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class LoginDefenseCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCheckLoginDefenseWhenWrongPasswordUsed()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $username = 'tenant11@example.com';
        $wrongPassword = 'pass1';
        $correctPassword = 'pass';

        /** Login with wrong password first time **/
        $this->login($username, $wrongPassword);
        $this->assertNotNull(
            $loginError = $this->page->find('css', '.login-error'),
            'User should see login error when using wrong password'
        );
        $this->assertEquals('login.error.msg', $loginError->getText());

        /** Login with wrong password second time **/
        $this->login($username, $wrongPassword);
        $this->assertNotNull(
            $loginError = $this->page->find('css', '.login-error'),
            'User should see login error when using wrong password'
        );
        $this->assertEquals('login.error.msg', $loginError->getText());

        $this->login($username, $correctPassword);
        $this->assertNotNull(
            $loginError = $this->page->find('css', '.login-error'),
            'User should see login error after 2 failures when log in'
        );
        $this->assertEquals('login.defence', $loginError->getText());
        $this->session->wait(70000); // wait while login defense is expired

        $this->login($username, $correctPassword);
        $this->assertNotNull($this->page->find('css', '#current-payments'), 'Logged tenant should see contract list');
    }

    /**
     * @param string $username
     * @param string $password
     */
    protected function login($username, $password)
    {
        $this->session->visit($this->getUrl() . 'login');
        $this->assertNotNull($mainEl = $this->page->find('css', '#login_form'), 'Login form not found');
        $this->assertNotNull(
            $usernameEl = $this->page->find('css', '#username'),
            'Username field on login form not found'
        );
        $usernameEl->setValue($username);
        $this->assertNotNull(
            $passwordEl = $this->page->find('css', '#password'),
            'Password field on login form not found'
        );
        $passwordEl->setValue($password);
        $this->page->pressButton('_submit');
    }
}
