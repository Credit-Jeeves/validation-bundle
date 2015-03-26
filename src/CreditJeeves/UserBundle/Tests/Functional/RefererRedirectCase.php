<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class RefererRedirectCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldRedirectToRefererAfterLoginSuccess()
    {
        $user = 'tenant11@example.com';
        $password = 'pass';

        $this->setDefaultSession('selenium2');
        $this->load(true);

        $this->session->visit($this->getUrl() . 'sources/');
        $this->assertNotNull($mainEl = $this->page->find('css', '#login_form'), 'Login form does not found');
        $this->assertNotNull($usernameEl = $this->page->find('css', '#username'));
        $usernameEl->setValue($user);
        $this->assertNotNull($passwordEl = $this->page->find('css', '#password'));
        $passwordEl->setValue($password);
        $this->page->pressButton('_submit');

        $this->assertEquals($this->getUrl() . 'sources/', $this->session->getCurrentUrl());
    }

    /**
     * @test
     */
    public function shouldRedirectToDefaultUrlAfterLoginSuccessIfAccessDeniedForRefererUrl()
    {
        $user = 'landlord1@example.com';
        $password = 'pass';

        $this->setDefaultSession('selenium2');
        $this->load(true);

        $this->session->visit($this->getUrl() . 'sources/');
        $this->assertNotNull($mainEl = $this->page->find('css', '#login_form'), 'Login form does not found');
        $this->assertNotNull($usernameEl = $this->page->find('css', '#username'));
        $usernameEl->setValue($user);
        $this->assertNotNull($passwordEl = $this->page->find('css', '#password'));
        $passwordEl->setValue($password);
        $this->page->pressButton('_submit');

        $this->assertEquals($this->getUrl() . 'landlord/', $this->session->getCurrentUrl());
    }

}
