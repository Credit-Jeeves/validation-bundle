<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class CheckCase extends BaseTestCase
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
     */
    public function userCheckFail()
    {
        $this->setDefaultSession('selenium2');
        $this->load($this->fixtures, true);
        $this->userLoginCheck();
        $this->userFailAttempt();
        $this->userCheckError();
        $this->logout();
        $this->login('john@example.com', 'pass');
        $this->userCheckLock();
        $this->session->visit($this->getUrl());
        $this->userCheckLock();
        $this->logout();
    }

    /**
     * @test
     */
    public function userCheckSuccess()
    {
        $this->setDefaultSession('selenium2');
        $this->load($this->fixtures, true);
        $this->userLoginCheck();
        $this->userSuccessAttempt();
        $this->logout();
        $this->login('john@example.com', 'pass');
        $this->logout();
    }
    
    private function userLoginCheck()
    {
        $this->login('john@example.com', 'pass');
        $this->session->wait(
                $this->timeout + 5000,
                "jQuery('#entryForm form').children().length > 0"
        );
    }

    private function userFailAttempt()
    {
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.submit'));
        $this->fillForm(
                $form,
                array(
                        'questions_OutWalletAnswer1_0' => 1,
                        'questions_OutWalletAnswer2_0' => 1,
                        'questions_OutWalletAnswer3_0' => 1,
                        'questions_OutWalletAnswer4_0' => 1,
                )
        );
        $submit->click();
        $this->assertNotNull($title = $this->page->find('css', '.pod-large h1'));
        $this->assertEquals('pidkiq.title', $title->getText());
    }

    private function userSuccessAttempt()
    {
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.submit'));
        $this->fillForm(
                $form,
                array(
                        'questions_OutWalletAnswer1_0' => 1,
                        'questions_OutWalletAnswer2_1' => 1,
                        'questions_OutWalletAnswer3_2' => 1,
                        'questions_OutWalletAnswer4_3' => 1,
                )
        );
        $submit->click();
        $this->session->wait(
            $this->timeout + 5000,
            "jQuery('.score-current').length > 0"
        );
        $this->assertNotNull($score = $this->page->find('css', '.score-current'));
        $this->assertEquals(590, $score->getText(), 'Wrong score');
        $this->logout();
    }

    private function userCheckError()
    {
        $this->assertNotNull($message = $this->page->find('css', '.message-body'));
        $this->assertEquals('pidkiq.error.answers-help@creditjeeves.com', $message->getText());
    }

    private function userCheckLock()
    {
        $this->assertNotNull($message = $this->page->find('css', '.message-body'));
        $this->assertEquals('pidkiq.error.lock-help@creditjeeves.com', $message->getText());
    }
}
