<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class CheckCase extends BaseTestCase
{
    /**
     * @test
     */
    public function userCheckFail()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
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
        $this->load(true);
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
        $this->assertEquals(536, $score->getText(), 'Wrong score');
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
