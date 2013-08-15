<?php
namespace CreditJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * Dealer's login tests
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class BuyReportCase extends BaseTestCase
{

    protected $envPath = '/_test.php/';

    /**
     * @test
     */
    public function checkBuyReportBox()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('alex@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($text = $this->page->find('css', '#checkout_buy_box p'));
        $this->assertEquals('box-message', $text->getText());

        $this->logout();
    }

    /**
     * @test
     */
    public function checkCurrentDownloadedData()
    {
        $this->load(false);
        $this->setDefaultSession('symfony');

        $this->login('emilio@example.com', 'pass');

        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->modify('-1 days');
        
        $dateShortFormat = static::getContainer()->getParameter('date_short');
        $this->assertEquals(
            $oneMonthAgo->format($dateShortFormat),
            $date->getText()
        );

        $this->assertNotNull($text = $this->page->find('css', '#checkout_buy_box p'));
        $this->assertEquals('box-message-expired', $text->getText());

        $this->page->clickLink('tabs.report');
        $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->modify('-32 days');
        $this->assertEquals(
            $oneMonthAgo->format($dateShortFormat),
            $date->getText()
        );

        $this->logout();
    }

    /**
     * @test
     * @depends checkCurrentDownloadedData
     */
    public function authorizeNetAim()
    {
        $this->setDefaultSession('selenium2');
        $this->load(false);

        $this->login('emilio@example.com', 'pass');

        $this->clearEmail();

        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout, "$('#checkout_buy_box .button').length > 0");
        $this->page->clickLink('buy-link');

        $this->session->wait($this->timeout, "$('#checkout_authorize_net_aim_type').length > 0");
        $this->assertNotNull($form = $this->page->find('css', '#checkout_authorize_net_aim_type'));

        $form->pressButton('buy-report-form-submit');
        $this->assertCount(3, $form->findAll('css', '.error_list'), 'Number of errors is wrong');

        $formData = array(
            'order_authorize_authorize_card_num' => '0005105105105100',
            'order_authorize_authorize_card_code' => '000',
            'order_authorize_authorize_exp_date_month' => date('m'),
            'order_authorize_authorize_exp_date_year' => date('Y'),
        );

        // Fake data: card number
        $this->fillForm($form, $formData);
        $form->pressButton('buy-report-form-submit');

        $this->assertNotNull($globalErrors = $form->findAll('css', '.flash-error li'));
        $this->assertCount(1, $globalErrors);
        $this->assertEquals(
            'authorize-net-aim-error-main-message-3-authorize-net-aim-error-message-6-' .
            static::getContainer()->getParameter('support_email'),
            $globalErrors[0]->getText()
        );
        $formData['order_authorize_authorize_card_num'] = '4111111111111111';
        $this->fillForm($form, $formData);
        $form->pressButton('buy-report-form-submit');

        $this->session->wait(
            $this->timeout,
            "jQuery('#report_page').children().length > 0"
        );

        $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
        $dateShortFormat = static::getContainer()->getParameter('date_short');

        $this->assertEquals(date($dateShortFormat), $date->getText());
    }

    /**
     * @test
     * @depends authorizeNetAim
     */
    public function authorizeNetAimCheckEmail()
    {
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);

        $email->click();
        $this->assertNotNull($subject = $this->page->find('css', '#subject span'));
        $this->assertEquals('Receipt from Credit Jeeves', $subject->getText());
        $this->assertNotNull($body = $this->page->find('css', '#body'));

        $this->page->clickLink('text/html');

        $this->assertEquals(1, preg_match("/Reference Number: (.*)/", $this->page->getText(), $matches));
        $this->assertNotEmpty($matches[1]);
    }
}
