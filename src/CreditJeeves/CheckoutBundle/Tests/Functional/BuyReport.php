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

    protected $fixtures = array(
        '001_cj_account_group.yml',
        '003_cj_dealer_account.yml',
        '004_cj_applicant.yml',
        '005_cj_lead.yml',
        '006_cj_applicant_report.yml',
        '007_cj_applicant_score.yml',
        '010_cj_affiliate.yml',
        '011_cj_settings.yml',
        '013_cj_holding_account.yml',
        '019_atb_simulation.yml',
        '017_cj_order.yml',
        '018_cj_operation.yml',
        '020_email.yml',
        '021_email_translations.yml',
    );

    /**
     * @test
     */
    public function checkBuyReportBox()
    {
//    $this->setSession('webdriver');
        $this->load($this->fixtures, true);
        $this->setDefaultSession('symfony');
        $this->login('alex@example.com', 'pass');
        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($text = $this->page->find('css', '#checkout_buy_box p'));
        $this->assertEquals('box-message', $text->getText());

        $this->logout();
    }

    /**
     * @~test
     */
    public function checkCurrentDownloadedData()
    {
//    $this->setSession('webdriver');
        $this->load($this->fixtures, false);
        $this->setDefaultSession('symfony');

        $this->login('emilio@example.com', 'pass');

        $this->page->clickLink('tabs.summary');
        $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->modify('-1 days');
        $this->assertEquals(
            $oneMonthAgo->format('M j, Y'),
            $date->getText()
        );

        $this->assertNotNull($text = $this->page->find('css', '#checkout_buy_box p'));
        $this->assertEquals('box-message-expired', $text->getText());

        $this->page->clickLink('tabs.report');
        $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->modify('-32 days');
        $this->assertEquals(
            $oneMonthAgo->format('M j, Y'),
            $date->getText()
        );

        $this->logout();
    }

    /**
     * @test
     */
    public function authorizeNetAim()
    {
        $this->setDefaultSession('selenium2');
        $this->load($this->fixtures, false);

        $this->login('emilio@example.com', 'pass');

        $this->page->clickLink('tabs.summary');
        $this->page->clickLink('buy-link');

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

        $this->assertEquals(date('M j, Y'), $date->getText());
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
