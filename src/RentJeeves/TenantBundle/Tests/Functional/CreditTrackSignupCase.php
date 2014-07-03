<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * Tests CreditTrack code from the tenant's perspective
 */
class CreditTrackSignupCase extends BaseTestCase
{
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
                'rentjeeves_checkoutbundle_paymentaccounttype_PayorName' => 'Timothy APPLEGATE',
                'rentjeeves_checkoutbundle_paymentaccounttype_RoutingNumber' => '062202574',
                'rentjeeves_checkoutbundle_paymentaccounttype_AccountNumber' => '123245678',
                'rentjeeves_checkoutbundle_paymentaccounttype_ACHDepositType_0' => true,
            )
        );
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout * 2, "$('#id-pay-step').is(':visible')");
        $this->page->pressButton('checkout.make_payment');
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
    }

    public function enterSignupFlow()
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
}
