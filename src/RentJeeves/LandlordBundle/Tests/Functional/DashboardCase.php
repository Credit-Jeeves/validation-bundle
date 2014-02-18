<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class DashboardCase extends BaseTestCase
{
    /**
     * @test
     */
    public function sort()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#payments-block td'));
        // This was commented because by default we use sort by date
        //$this->assertEquals('order.status.text.new', $td[0]->getText(), 'Wrong text in field');

        $this->assertNotNull($status = $this->page->find('css', '#status'));
        $status->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#payments-block td'));
        $this->assertEquals('order.status.text.new', $td[0]->getText(), 'Wrong text in field');

        $this->assertNotNull($status = $this->page->find('css', '#status'));
        $status->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#payments-block td'));
        $this->assertEquals('order.status.text.returned', $td[0]->getText(), 'Wrong text in field');

        $this->assertNotNull($propertyA = $this->page->find('css', '#propertyA'));
        $propertyA->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#actions-block td'));

        $this->logout();
    }

    /**
     * @test
     */
    public function search()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (29)', $allh2->getText(), 'Wrong count');

        $this->assertNotNull($searchPayments_link = $this->page->find('css', '#searchPayments_link'));
        $searchPayments_link->click();
        $this->assertNotNull($tenant = $this->page->find('css', '#searchPayments_li_2'));
        $tenant->click();
        $this->assertNotNull($searchField = $this->page->find('css', '#searsh-field-payments'));
        $searchField->setValue('John');
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', 'h3.processPayment'));
        $this->assertEquals('donthavedata', $allh2->getText(), 'Wrong count');

        $this->assertNotNull($delete = $this->page->find('css', '#payments-block .pie-el'));
        $delete->click();

        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (29)', $allh2->getHtml(), 'Wrong count');

        $this->logout();
    }

    /**
     * @test
     */
    public function groupByDeposit()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($searchPayments_link = $this->page->find('css', '#searchPayments_link'));
        $searchPayments_link->click();
        $this->assertNotNull($deposit = $this->page->find('css', '#searchPayments_li_4'));
        $deposit->click();

        $this->session->wait($this->timeout, "$('#search-submit-deposit-status').is(':visible')");
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-deposit-status'));
        $searchSubmit->click();

        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'));
        // the test should check payments.batch_deposits, but selenium doesn't know about this text
        // the main goal is to check the amount
        $this->assertEquals('payments.total (3)', $title->getHtml());

        $this->logout();
    }
}
