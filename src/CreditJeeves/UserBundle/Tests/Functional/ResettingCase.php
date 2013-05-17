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
        '020_email.yml',
        '021_email_translations.yml',
    );

    /**
     * @test
     * @~expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function resettingPassword()
    {
        $this->markTestIncomplete('Finish');
        $this->load($this->fixtures, true);
        $this->setDefaultSession('goutte');
        $this->session->visit($this->getUrl() . 'login');
        $this->page->clickLink('login.resetting.link');

        $form = $this->page->find('css', '#fos_user_resetting_request');
        $this->assertNotNull($form);

        $this->fillForm($form, array('username' => 'emilio@example.com'));
        $form->pressButton('resetting.request.submit');

        $this->assertNotNull($title = $this->page->find('css', 'h1'));
        $this->assertEquals('resetting.check_email.title', $title->getText());

        $this->visitEmailsPage();

        $this->assertNotNull($links = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $links);

        $this->page->clickLink('email_1');

        $this->assertNotNull($subject = $this->page->find('css', 'h1'));
        $this->assertEquals('Reset Password', $subject->getText());

        $this->page->clickLink('text/html');

        $this->assertEquals(1, preg_match("/Password:(.*)/", $this->page->getText(), $matches));
        $this->assertNotEmpty($matches[1]);

        $this->session->visit($matches[1]);

        sleep(10);

        $this->logout();
    }
}
