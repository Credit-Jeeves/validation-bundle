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
        $this->assertEquals('order.status.text.new', $td[0]->getText(), 'Wrong text in field');

        $this->assertNotNull($status = $this->page->find('css', '#status'));
        $status->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#payments-block td'));
        $this->assertEquals('order.status.text.returned', $td[0]->getText(), 'Wrong text in field');

        $this->assertNotNull($status = $this->page->find('css', '#status'));
        $status->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '#payments-block td'));
        $this->assertEquals('order.status.text.new', $td[0]->getText(), 'Wrong text in field');

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
        $this->assertEquals('payments.total (40)', $allh2->getText(), 'Wrong count');

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
        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (7)', $allh2->getText(), 'Wrong count');

        $this->assertNotNull($delete = $this->page->find('css', '#payments-block .pie-el'));
        $delete->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");

        $this->assertNotNull($allh2 = $this->page->find('css', '#payments-block .title-box>h2'));
        $this->assertEquals('payments.total (40)', $allh2->getText(), 'Wrong count');

        $this->logout();
    }
}
