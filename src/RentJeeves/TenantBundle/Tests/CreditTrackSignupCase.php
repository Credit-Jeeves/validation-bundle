<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class SummaryCase extends BaseTestCase
{
    /**
     * @test
     */
    public function basicSignup()
    {
        self::$kernel = null;
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('marion@rentrack.com', 'pass');

        $this->page->clickLink('tabs.summary');
        $this->session->wait($this->timeout, "$('.show-credittrack-pricing-popup').is(':visible')");

        $this->page->clickLink('credittrack.buy.link');
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");

        $this->page->pressButton('popup.sign.up.today');
        $this->session->wait($this->timeout, "$('#id-source-step').is(':visible')");

        // click the first payment type
        $this->page->find('css', '#id-source-step .payment-accounts label:nth-of-type(1)')->click();
        $this->page->pressButton('pay_popup.step.next');
        $this->session->wait($this->timeout, "$('#id-pay-step').is(':visible')");

        $this->page->pressButton('checkout.make_payment');
    }
}
