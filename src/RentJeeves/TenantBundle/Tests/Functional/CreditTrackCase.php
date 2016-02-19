<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Settings;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class CreditTrackCase extends BaseTestCase
{
    // should set more b/c a lot of operations for waiting
    protected $timeout = 20000; // 20 s

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
        $title = $this->getDomElement(
            '#summary_page p.credit-balance-title span.floatright',
            'Summary report title should be visible'
        );
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

        $this->session->wait($this->timeout, '!$(".overlay").is(":visible");');
        $link = $this->page->find('css', '#id-source-step .payment-accounts a.checkout-plus');
        $link->click();

        $this->makeNew();

        $this->page->pressButton('checkout.make_payment');

        $this->session->wait($this->timeout, '!$(".overlay").is(":visible");');

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

        $this->session->wait($this->timeout, '!$(".overlay").is(":visible");');
        $existingAccounts = $this->getDomElements('#id-source-step .payment-accounts label.checkbox.radio');
        $this->assertCount(2, $existingAccounts, 'Expected 2 existing payment accounts');
        $existingAccounts[0]->click();


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

        // for removing PaymentAccount
        $payment = $this->getEntityManager()->getRepository('RjDataBundle:Payment')->find(5);
        $payment->setStatus(PaymentStatus::CLOSE);
        $this->getEntityManager()->flush($payment);

        $rows = $this->getDomElements('#payment-account-table tbody tr');

        $this->assertCount(2, $rows, "Should be exist 2 payment accounts");

        $rows[0]->clickLink('delete');
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');
        $this->session->wait($this->timeout, "1 == jQuery('#payment-account-table tbody tr').length");

        $rows = $this->getDomElements('#payment-account-table tbody tr');

        $this->assertCount(1, $rows, "Should be removed 1 payment account");

        $rows[0]->clickLink('delete');
        $this->session->wait($this->timeout, "jQuery('#payment-account-delete:visible').length");
        $this->page->clickLink('payment_account.delete.yes');
        $this->session->wait($this->timeout, "1 == jQuery('#payment-account-table').length");

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

    /**
     * @test
     */
    public function scoreTrackFreeUntil3Month()
    {
        $this->load(true);
        /** @var Settings $projectSettings */
        $projectSettings = $this->getEntityManager()->getRepository('DataBundle:Settings')->findOneBy([]);
        $this->assertNotEmpty($projectSettings, 'We should have settings');
        $projectSettings->setScoretrackFreeUntil(3);
        $orders = $this->getEntityManager()->getRepository('DataBundle:Order')->findAll();
        $this->assertCount(52, $orders, 'We should have in fixtures 52 orders');
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneByEmail('transU@example.com');
        $this->assertEmpty(
            $tenant->getSettings()->getScoretrackFreeUntil(),
            'ScoreTrack Should be empty option in fixture'
        );
        $this->assertEmpty(
            $tenant->getSettings()->getCreditTrackPaymentAccount(),
            'PaymentAccount Should empty by default'
        );
        $this->assertEmpty(
            $tenant->getSettings()->getCreditTrackEnabledAt(),
            'CreditTrack Should empty by default'
        );
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        $this->setDefaultSession('selenium2');
        $this->login('transU@example.com', 'pass');

        $this->page->clickLink('tabs.summary');
        $this->assertNotEmpty(
            $freeForMonth = $this->page->find('css', '.free_for_month'),
            'Should see element where we show: how long scoretrack should be free'
        );
        $this->assertEquals('credittrack.promo.free', $freeForMonth->getText(), 'Text for trans should be free');
        $this->page->clickLink('credittrack.buy.link');
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");
        $this->page->pressButton('popup.sign.up.today');
        $this->assertNotEmpty(
            $informationBoxes = $this->page->findAll('css', '.information-box'),
            'Should see element where we show: info message about free'
        );
        $this->assertCount(2, $informationBoxes, 'We should have two boxes with info message.');
        $this->assertEquals('credittrack.message_for_payment.first_step', $informationBoxes[0]->getText());
        $this->session->wait($this->timeout, "$('#id-source-step').is(':visible')");
        $this->makeNew();
        $this->assertCount(2, $informationBoxes, 'We should have two boxes with info message.');
        $this->assertEquals('credittrack.message_for_payment.second_step', $informationBoxes[1]->getText());
        $this->page->pressButton('checkout.make_payment');
        $this->checkReport();
        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->findOneByEmail('transU@example.com');
        $this->assertNotEmpty($tenant->getSettings()->getScoretrackFreeUntil(), 'Should be filled with Date');
        $scoreTrackUntil = new \DateTime('+3 month');
        $scoreTrackUntil->setTime(0, 0, 0);
        $scoreTrackUntilInDb = $tenant->getSettings()->getScoretrackFreeUntil();
        $this->assertEquals(
            $scoreTrackUntil->format(\DateTime::ISO8601),
            $scoreTrackUntilInDb->format(\DateTime::ISO8601),
            'ScoreTrack free was not setup'
        );
        $this->assertNotEmpty($tenant->getSettings()->getCreditTrackPaymentAccount(), 'Should set payment account');
        $this->assertNotEmpty($tenant->getSettings()->getCreditTrackEnabledAt(), 'Should be set enabled at');
        $this->getEntityManager()->clear();
        $orders = $this->getEntityManager()->getRepository('DataBundle:Order')->findAll();
        $this->assertCount(52, $orders, 'We should not create order and have exec like in fixtures');
    }
}
