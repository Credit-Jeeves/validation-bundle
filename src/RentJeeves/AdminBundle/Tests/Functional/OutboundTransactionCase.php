<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class OutboundTransactionCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldShowOutboundTransactionsForPayDirectOrders()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_orders'), 'Block Orders not found');

        $tableBlock->clickLink('link_list');
        $this->assertNotNull($pagination = $this->page->find('css', 'div.pagination'), 'Pages not found');
        $pagination->clickLink('3'); // go to 3d page - there is pay_direct order

        $this->assertNotNull($outbound = $this->page->findAll('css', 'a.outbound'), 'Outbound button not found');
        $this->assertCount(1, $outbound, 'One OutboundLeg button expected');
        $outbound[0]->click();

        $this->assertNotNull($rows = $this->page->findAll('css', 'form>table>tbody>tr'), 'Outbound table not found');
        $this->assertCount(2, $rows, '2 rows in OutboundTransactions expected');
    }
}
