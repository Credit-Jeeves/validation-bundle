<?php
namespace CreditJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
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
        // @todo remove logic
//         $this->page->clickLink('tabs.summary');
//         $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
//         $oneMonthAgo = new \DateTime();
//         $oneMonthAgo->modify('-1 days');
        
//         $dateShortFormat = static::getContainer()->getParameter('date_short');
//         $this->assertEquals(
//             $oneMonthAgo->format($dateShortFormat),
//             $date->getText()
//         );

//         $this->assertNotNull($text = $this->page->find('css', '#checkout_buy_box p'));
//         $this->assertEquals('box-message-expired', $text->getText());

//         $this->page->clickLink('tabs.report');
//         $this->assertNotNull($date = $this->page->find('css', '.pod-large .datetime.floatright'));
//         $oneMonthAgo = new \DateTime();
//         $oneMonthAgo->modify('-32 days');
//         $this->assertEquals(
//             $oneMonthAgo->format($dateShortFormat),
//             $date->getText()
//         );

        $this->logout();
    }

    /**
     * @test
     * depends checkCurrentDownloadedData
     */
    public function authorizeNetAim()
    {
        $this->markTestSkipped('This is an outdated test. Probably can be removed.');
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
        $this->assertCount(3, $form->findAll('css', '.error_list li'), 'Number of errors is wrong');

        $formData = array(
            'order_authorize_authorizes_0_card_num'       => '0005105105105100',
            'order_authorize_authorizes_0_card_code'      => '002',
            'order_authorize_authorizes_0_exp_date_month' => date('m'),
            'order_authorize_authorizes_0_exp_date_year'  => date('Y'),
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

        $formData['order_authorize_authorizes_0_card_num'] = '4111111111111111';
        $i = 4;
        $date = null;
        while ($i--) {
            $formData['order_authorize_authorizes_0_card_code'] = '00' . $i;
            $month = ($month = date('m') + $i) <= 12?$month:$month - 11;
            $formData['order_authorize_authorizes_0_exp_date_month'] = 2 == strlen($month)? $month : '0' . $month;
            $formData['order_authorize_authorizes_0_exp_date_year'] = date('Y') + $i;
            $this->fillForm($form, $formData);
            $form->pressButton('buy-report-form-submit');

            if (!($globalErrors = $form->findAll('css', '.flash-error li')) ||
                'authorize-net-aim-error-main-message-3--' .
                static::getContainer()->getParameter('support_email') != $globalErrors[0]->getText()
            ) {
                $this->session->wait(
                    $this->timeout + 10000,
                    "jQuery('.pod-large .datetime.floatright').length > 0"
                );
            }
            if ($date = $this->page->find('css', '.pod-large .datetime.floatright')) {
                break;
            }
        }

        $this->assertNotNull($date);
        $this->assertEquals(date(static::getContainer()->getParameter('date_short')), $date->getText());

        $emails = $this->getEmails();
        $this->assertCount(2, $emails, 'Wrong number of emails');

        $email = $this->getEmailReader()->getEmail(array_shift($emails))->getMessage('text/html');
        $this->assertEquals('Receipt from Credit Jeeves', $email->getSubject());
        $this->assertEquals(1, preg_match("/Reference Number: (.*)/", $email->getBody(), $matches));
        $this->assertNotEmpty($matches[1]);
    }
}
