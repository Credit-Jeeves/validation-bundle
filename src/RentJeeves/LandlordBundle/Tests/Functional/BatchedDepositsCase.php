<?php

namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class BatchedDepositsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldShowBatchedDeposits()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        $this->page->clickLink('accounting.menu.batched_deposits');

        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");

        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'), 'Title not found');
        $this->assertEquals(
            'accounting.menu.batched_deposits (5)',
            $title->getHtml(),
            'Batched Deposits should have 5 items'
        );
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.properties-table>tbody>tr'),
            'Lines in table not found'
        );
        $this->assertCount(14, $rows, 'Table should contain 14 rows');

        $this->assertNotNull(
            $filter = $this->page->find('css', '#depositTypeStatus_link'),
            'Link for filter by payment type not found'
        );
        $filter->click();
        $this->assertNotNull(
            $bankFilter = $this->page->find('css', '#depositTypeStatus_li_1'),
            'Link for filter by bank not found'
        );
        $bankFilter->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");

        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'), 'Title not found');
        $this->assertEquals(
            'accounting.menu.batched_deposits (0)',
            $title->getHtml(),
            'Batched Deposits should have 0 bank items'
        );
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.properties-table>tbody>tr'),
            'Lines in table not found'
        );
        $this->assertCount(0, $rows, 'Table should contain 0 rows for \'bank\' filter');

        $filter->click();
        $this->assertNotNull(
            $cardFilter = $this->page->find('css', '#depositTypeStatus_li_2'),
            'Link for filter by card not found'
        );
        $cardFilter->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");

        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'), 'Title not found');
        $this->assertEquals(
            'accounting.menu.batched_deposits (5)',
            $title->getHtml(),
            'Batched Deposits should have 5 card items'
        );
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.properties-table>tbody>tr'),
            'Lines in table not found'
        );
        $this->assertCount(14, $rows, 'Table should contain 25 rows for \'card\' filter');
    }
}
