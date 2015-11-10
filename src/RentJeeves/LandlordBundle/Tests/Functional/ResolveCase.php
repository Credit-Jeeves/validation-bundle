<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\CoreBundle\DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ResolveCase extends BaseTestCase
{
    const CONTRACTS_COUNT = 3;

    /**
     * @test
     */
    public function resolveEmail()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');

        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(static::CONTRACTS_COUNT, $resolve, 'Wrong number of resolve contracts');
        $resolve[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#blockPopupEditProperty button.button'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve-late').is(':visible')");
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(static::CONTRACTS_COUNT, $resolve, 'Wrong number of resolve contracts');
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }

    /**
     * @test
     */
    public function resolvePaid()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(static::CONTRACTS_COUNT, $resolve, 'Wrong number of resolve contracts');
        $resolve[0]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve-late').is(':visible')");
        $this->assertNotNull($checkboxes = $this->page->findAll('css', '#contract-resolve-late .checkbox'));
        $this->assertCount(2, $checkboxes, 'Wrong number of checkboxes');
        $checkboxes[1]->click();
        $this->assertNotNull(
            $inputs = $this->page->findAll('css', '#contract-resolve-late input[type=text]')
        );
        $this->assertCount(2, $inputs, 'Wrong number of inputs');
        $date = new DateTime();
        date_time_set($date, 0, 0);
        $date->modify('-15 year');
        $inputs[1]->setValue($date->format('m/d/Y'));
        $this->assertNotNull($buttons = $this->page->findAll('css', '#blockPopupEditProperty button.button'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve-late').is(':visible')");
        $this->session->wait($this->timeout, "!jQuery('#actions-block .processPayment').is(':visible')");
        $this->assertNotNull($contracts = $this->page->findAll('css', '#actions-block table tbody tr'));
        $this->assertCount(static::CONTRACTS_COUNT - 1, $contracts);
        $this->logout();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $operations = $em->getRepository('DataBundle:Operation')->findBy(
            array(
                'createdAt' => $date,
            )
        );
        $this->assertCount(1, $operations, 'Wrong count operation');
        $orders = $em->getRepository('DataBundle:Order')->findBy(
            array(
                'created_at' => $date,
            )
        );
        $this->assertCount(1, $orders, 'Wrong count order');
    }

    /**
     * @test
     */
    public function resolveUnpaid()
    {
        $this->markTestIncomplete('Functional is not ready');
    }

    /**
     * @test
     */
    public function monthToMonth()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');

        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(static::CONTRACTS_COUNT, $resolve, 'Wrong number of resolve contracts');
        $resolve[static::CONTRACTS_COUNT - 1]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve-ended').is(':visible')");
        $this->assertNotNull($checkboxes = $this->page->findAll('css', '#contract-resolve-ended .checkbox'));
        $this->assertCount(3, $checkboxes, 'Wrong number of checkboxes');
        $checkboxes[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve-ended').is(':visible')");
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $buttons = $this->page->findAll('css', '#contract-resolve-ended .footer-button-box button')
        );
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve-ended').is(':visible')");
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull($contracts = $this->page->findAll('css', '#actions-block table tbody tr'));
        $this->assertCount(static::CONTRACTS_COUNT - 1, $contracts);
        $this->logout();
    }

    /**
     * @test
     */
    public function changeEndDate()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(static::CONTRACTS_COUNT, $resolve, 'Wrong number of resolve contracts');
        $resolve[static::CONTRACTS_COUNT - 1]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve-ended').is(':visible')");
        $this->assertNotNull($checkboxes = $this->page->findAll('css', '#contract-resolve-ended .checkbox'));
        $this->assertCount(3, $checkboxes, 'Wrong number of checkboxes');
        $checkboxes[1]->click();
        $this->assertNotNull($inputs = $this->page->findAll('css', '#contract-resolve-ended input[type=text]'));
        $this->assertCount(2, $inputs, 'Wrong number of inputs');
        $date = new DateTime();
        $date->modify('+18 year');
        $inputs[0]->setValue($date->format('m/d/Y'));
        $this->assertNotNull(
            $buttons = $this->page->findAll('css', '#contract-resolve-ended .footer-button-box button')
        );
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve').is(':visible')");
        $this->session->wait($this->timeout, "!jQuery('#actions-block .processPayment').is(':visible')");
        $this->assertNotNull($contracts = $this->page->findAll('css', '#actions-block table tbody tr'));
        $this->assertCount(static::CONTRACTS_COUNT - 1, $contracts);
        $this->logout();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'finishAt' => $date,
            )
        );
        $this->assertCount(1, $contracts, 'Wrong count contract');
    }

    /**
     * @test
     */
    public function markAsFinished()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "jQuery('#actions-block table tbody tr').length > 0");
        $this->assertNotNull(
            $resolve = $this->page->findAll('css', '#actions-block table tbody tr td a.action-alert')
        );
        $this->assertCount(static::CONTRACTS_COUNT, $resolve, 'Wrong number of resolve contracts');
        $resolve[static::CONTRACTS_COUNT - 1]->click();
        $this->session->wait($this->timeout, "jQuery('#contract-resolve-ended').is(':visible')");
        $this->assertNotNull($checkboxes = $this->page->findAll('css', '#contract-resolve-ended .checkbox'));
        $this->assertCount(3, $checkboxes, 'Wrong number of checkboxes');
        $checkboxes[2]->click();
        $this->assertNotNull(
            $inputs = $this->page->findAll('css', '#contract-resolve-ended input[type=text]')
        );
        $this->assertCount(2, $inputs, 'Wrong number of inputs');
        $inputs[1]->setValue(7000);
        $this->assertNotNull(
            $buttons = $this->page->findAll('css', '#contract-resolve-ended .footer-button-box button')
        );
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!jQuery('#contract-resolve-ended').is(':visible')");
        $this->session->wait($this->timeout, "!jQuery('#actions-block .processPayment').is(':visible')");
        $this->assertNotNull($contracts = $this->page->findAll('css', '#actions-block table tbody tr'));
        $this->assertCount(static::CONTRACTS_COUNT - 1, $contracts);
        $this->logout();
        //Check email notify tenant about removed contract by landlord
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(
            array(
                'uncollectedBalance' => 7000,
                'status'             => 'finished',
            )
        );
        $this->assertCount(1, $contracts, 'Wrong count contract');
    }
}
