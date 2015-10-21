<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class CreditTrackCase extends BaseTestCase
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
        $this->session->wait($this->timeout, '$("h4:contains(\'common.loading.text\')").length');
        $this->session->wait($this->timeout * 3, "$('#summary_page p.credit-balance-title').is(':visible')");
        $this->assertNotNull($title = $this->page->find('css', '#summary_page p.credit-balance-title span.floatright'));
        $dateUpdating = new \DateTime();
        $this->assertEquals('common.date ' . $dateUpdating->format('M j, Y'), $title->getText());
    }

    protected function makeNew()
    {
        $form = $this->page->find('css', '#rentjeeves_checkoutbundle_paymentaccounttype');
        $this->fillForm(
            $form,
            array(
                'rentjeeves_checkoutbundle_paymentaccounttype_name' => 'New test payment',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardAccountName' => 'John Adams',
                'rentjeeves_checkoutbundle_paymentaccounttype_CardNumber' => '5473500000000014',
                'rentjeeves_checkoutbundle_paymentaccounttype_VerificationCode' => '432',
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationMonth' => date('n'),
                'rentjeeves_checkoutbundle_paymentaccounttype_ExpirationYear' => date('Y') + 2,
            )
        );
        $this->assertNotNull(
            $addresses = $form->findAll('css', '#rentjeeves_checkoutbundle_paymentaccounttype_address_choice_box i')
        );
        $addresses[0]->click();
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout * 3, "$('#id-pay-step').is(':visible')");
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

        $this->makeNew();

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

    /**
     * @test
     * @depends existingAccountSignup
     */
    public function edit()
    {
        $this->page->clickLink('tabs.rent');
        $this->page->clickLink('rent.sources');

        $this->session->wait($this->timeout, "jQuery('#payment-account-table').length");
        $this->assertNotNull($rows = $this->page->findAll('css', '#payment-account-table tbody tr'));
        $this->assertCount(2, $rows);

        $rows[0]->clickLink('delete');
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');
        $this->session->wait($this->timeout, "1 == jQuery('#payment-account-table tbody tr').length");

        $rows[0]->clickLink('delete');
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');
        $this->session->wait($this->timeout, "1 == jQuery('#payment-account-table tbody tr').length");

        $this->page->clickLink('common.account');
        $this->page->clickLink('settings.plans');
        $this->page->clickLink('settings.plans.update');
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");

        $this->page->pressButton('popup.edit');
        $this->session->wait($this->timeout, "$('#id-source-step').is(':visible')");

        $this->makeNew();
        $this->page->pressButton('credittrack.pay.save');
        $this->session->wait($this->timeout, "jQuery('.flash-notice:visible').length");

        $this->assertNotNull($flash = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('credittrack.pay.saved', $flash->getText());

    }

    /**
     * @test
     * @depends edit
     */
    public function chooseFree()
    {
        $this->page->clickLink('settings.plans.update');
        $this->page->pressButton('popup.cancel');
        $this->page->clickLink('settings.plans.cancel.yes');
//        $this->session->wait($this->timeout, "jQuery('#current-plan:visible').text() = 'settings.plans.free'");

        $this->assertNotNull($plan = $this->page->find('css', '#current-plan'));
        $this->assertEquals('settings.plans.free', $plan->getText());
    }
}
