<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\Functional\BaseTestCase;

class ResettingCase extends BaseTestCase
{
    protected $fixtures = array(
        '001_cj_account_group.yml',
        '002_cj_admin_account.yml',
        '003_cj_dealer_account.yml',
        '004_cj_applicant.yml',
        '005_cj_lead.yml',
        '006_cj_applicant_report.yml',
        '007_cj_applicant_score.yml',
        '010_cj_affiliate.yml',
        '013_cj_holding_account.yml',
        '019_atb_simulation.yml',
        '020_email.yml',
        '021_email_translations.yml',
    );

    /**
     * @test
     */
    public function resettingPassword()
    {
        $this->setDefaultSession('symfony');
        $this->setDefaultSession('goutte');
//        $this->setDefaultSession('selenium2');
        $this->load($this->fixtures, true);
        $this->session->visit($this->getUrl() . 'login');

        $this->page->clickLink('login.resetting.link');

        $form = $this->page->find('css', '#fos_user_resetting_request');
        $this->assertNotNull($form);

        $this->fillForm($form, array('username' => 'emilio@example.com'));
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
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();

        $this->assertNotNull($links = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $links);

        $this->page->clickLink('email_1');

        $this->assertNotNull($subject = $this->page->find('css', '#subject span'));
        $this->assertEquals('Reset Password', $subject->getText());

        $this->page->clickLink('text/html');

        $this->assertEquals(
            1,
            preg_match("/To reset your password - please visit ([^ ]*) /", $this->page->getText(), $matches)
        );
        $this->assertNotEmpty($matches[1]);
        die('OK');
//        $this->setDefaultSession('symfony');
//        $this->setDefaultSession('selenium2');
        $this->session->visit($matches[1]);
    }
//
    /**
     * @test
     * @depends checkEmail
     */
    public function fillPassword()
    {
        $this->markTestIncomplete('FINISH');
        $form = $this->page->find('css', '#fos_user_resetting_form');
        $this->assertNotNull($form);

        $this->fillForm(
            $form,
            array(
                'fos_user_resetting_form_new_first' => '123',
                'fos_user_resetting_form_new_second' => '123',
            )
        );
        $this->page->pressButton('resetting.request.submit');

        $this->assertNotNull($activeTab = $this->page->find('css', '.header-tabs active first a'));

        $this->assertEquals('tabs.action_plan', $activeTab->getText());

        $this->logout();

        $this->login('emilio@example.com', '123');
        $this->logout();
    }
}
