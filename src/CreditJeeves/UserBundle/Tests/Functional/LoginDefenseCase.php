<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class LoginDefenseCase extends BaseTestCase
{
    /**
     * @test
     */
    public function checkError()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->session->visit($this->getUrl() . 'login');

        for ($i = 1; $i <= 2; $i++) {
            $this->assertNotNull($mainEl = $this->page->find('css', '#login_form'), 'Login form does not found');
            $this->assertNotNull($usernameEl = $this->page->find('css', '#username'));
            $usernameEl->setValue('emilio@example.com');
            $this->assertNotNull($passwordEl = $this->page->find('css', '#password'));
            $passwordEl->setValue('pass1');
            $this->page->pressButton('_submit');
        }

        $this->assertNotNull($mainEl = $this->page->find('css', '#login_form'), 'Login form does not found');
        $this->assertNotNull($usernameEl = $this->page->find('css', '#username'));
        $usernameEl->setValue('emilio@example.com');
        $this->assertNotNull($passwordEl = $this->page->find('css', '#password'));
        $passwordEl->setValue('pass');
        $this->page->pressButton('_submit');

        $this->assertNotNull($loginError = $this->page->find('css', '.login-error'));
        $this->assertEquals('login.defence', trim($loginError->getHtml()));
        $this->session->wait(70000); //wait ~1 minutes

        $this->login('emilio@example.com', 'pass');
        $this->logout();
    }
}
