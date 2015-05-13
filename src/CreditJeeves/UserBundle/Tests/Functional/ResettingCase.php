<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class ResettingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function resettingPassword()
    {
        $this->setDefaultSession('symfony');
//        $this->setDefaultSession('goutte');
//        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'login');

        $this->page->clickLink('login.resetting.link');

        $form = $this->page->find('css', '#fos_user_resetting_request');
        $this->assertNotNull($form);

        $this->fillForm($form, array('username' => 'tenant11@example.com'));
        $form->pressButton('resetting.request.submit');

        $this->assertNotNull($title = $this->page->find('css', 'h1'));
        $this->assertEquals('resetting.check_email.title', $title->getText());

    }

    /**
     * @test
     * @depends resettingPassword
     */
    public function checkEmail()
    {
        $emails = $this->getEmails();
        $this->assertCount(1, $emails);

        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $this->assertEquals('Reset Password', $email->getSubject());

        $this->assertEquals(
            1,
            preg_match(
                "/.*href=\"(.*)\".*Click here to change your password./is",
                $email->getBody(),
                $matches
            )
        );
        $this->assertNotEmpty($matches[1]);
        $this->session->visit($matches[1]);
    }
//
    /**
     * @test
     * @depends checkEmail
     */
    public function fillPassword()
    {
        $this->markTestIncomplete('FINISH'); //FIXME cli url generation!!!
        $form = $this->page->find('css', '#fos_user_resetting_form');
        $this->assertNotNull($form);

        $this->fillForm(
            $form,
            array(
                'fos_user_resetting_form_new_first' => '123',
                'fos_user_resetting_form_new_second' => '123',
            )
        );
        $this->page->pressButton('resetting.reset.submit');

        $this->assertNotNull($title = $this->page->find('css', 'h1'));

        $this->assertEquals('current_contracts.title', $title->getText());

        $this->logout();

        $this->login('tenant11@example.com', '123');
        $this->logout();
    }
}
