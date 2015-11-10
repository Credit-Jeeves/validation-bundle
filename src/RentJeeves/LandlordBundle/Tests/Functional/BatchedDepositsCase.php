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
            'accounting.menu.batched_deposits (7)',
            $title->getHtml(),
            'Batched Deposits should have 7 items'
        );
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.properties-table>tbody>tr'),
            'Lines in table not found'
        );
        $this->assertCount(22, $rows, 'Table should contain 22 rows');

        $this->assertNotNull(
            $filter = $this->page->find('css', '#depositTypeStatus_link'),
            'Link for filtering not found'
        );
        $filter->click();
        $this->assertNotNull(
            $batchFilter = $this->page->find('css', '#depositTypeStatus_li_2'),
            'Link for filtering by batch id not found'
        );
        $batchFilter->click();
        $this->assertNotNull(
            $searchInput = $this->page->find('css', '#search-field'),
            'Search text input not found'
        );
        $searchInput->setValue('555000');
        $this->assertNotNull(
            $submit = $this->page->find('css', '#search-submit-payments-status'),
            'Submit button not found'
        );
        $submit->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");

        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'), 'Title not found');
        $this->assertEquals(
            'accounting.menu.batched_deposits (1)',
            $title->getHtml(),
            'Batched Deposits should have 1 batch with ID#555000'
        );
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.properties-table>tbody>tr'),
            'Lines in table not found'
        );
        $this->assertCount(3, $rows, 'Table should contain 3 rows for \'batch ID\' filter');

        $filter->click();
        $this->assertNotNull(
            $transactionFilter = $this->page->find('css', '#depositTypeStatus_li_1'),
            'Link for filtering by transaction ID not found'
        );
        $transactionFilter->click();
        $searchInput->setValue('456456');
        $submit->click();
        $this->session->wait($this->timeout, "$('#processPayment').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processPayment').is(':visible')");

        $this->assertNotNull($title = $this->page->find('css', '#payments-block .title-box>h2'), 'Title not found');
        $this->assertEquals(
            'accounting.menu.batched_deposits (1)',
            $title->getHtml(),
            'Batched Deposits should have 1 transaction with ID #456456'
        );
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.properties-table>tbody>tr'),
            'Lines in table not found'
        );
        // 4 for transactions and 1 for batch
        $this->assertCount(5, $rows, 'Table should contain 2 rows for \'transaction ID\' filter');
    }
}
