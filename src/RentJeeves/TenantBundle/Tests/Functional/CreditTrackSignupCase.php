<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * Tests CreditTrack code from the tenant's perspective
 */
class CreditTrackSignupCase extends BaseTestCase
{
    protected function enterSignupFlow()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('marion@rentrack.com', 'pass');

        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout, "$('.show-credittrack-pricing-popup').is(':visible')");

        $this->page->clickLink('credittrack.buy.link');
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");

        $this->page->pressButton('popup.sign.up.today');
        $this->session->wait($this->timeout, "$('#id-source-step').is(':visible')");
    }

    protected function checkReport()
    {
        $this->session->wait($this->timeout, "$('.loading center').is(':visible')");
        $this->session->wait($this->timeout * 3, "$('#report_page .summary h3').is(':visible')");
        $this->assertNotNull($title = $this->page->find('css', '#report_page .summary h3'));
        $this->assertEquals('component.credit.summary', $title->getText());
    }

    /**
     * Creates a new payment account and uses it to sign up for CreditTrack
     *
     * @test
     *
     * @return void
     */
    public function newAccountSignup()
    {
        $this->enterSignupFlow();
        $this->page->clickLink('payment.account.new');

        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'Test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '123',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 1,
            )
        );
        $this->assertNotNull(
            $addresses = $form->findAll('css', '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i')
        );
        $addresses[0]->click();
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout * 3, "$('#id-pay-step').is(':visible')");
        $this->page->pressButton('checkout.make_payment');

        $this->checkReport();
    }

    /**
     * Registers an existing payment account for the RentTrackCorp merchant and
     * uses it to sign up for CreditTrack
     *
     * @test
     *
     * @return void
     */
    public function existingAccountSignup()
    {
        $this->enterSignupFlow();
        $this->page->find('css', '#id-source-step .payment-accounts label:nth-of-type(1)')->click();
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "$('#id-pay-step').is(':visible')");

        $this->page->pressButton('checkout.make_payment');

        $this->checkReport();
    }
}
