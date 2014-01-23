<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class PaymentCase extends BaseTestCase
{
    /**
     * @test
     */
    public function filter()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($block = $this->page->find('css', '#id_block_paymnets'));
        $block->clickLink('link_list');

        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->assertTrue(1 < count($table->findAll('css', 'tbody tr')));

        $this->page->fillField('filter_startDate_value_day', date('j')-1?:2);
        $this->page->pressButton('btn_filter');

        $this->assertNotNull($notice = $this->page->find('css', 'p.notice'));
        $this->assertEquals('no_result', $notice->getText());

    }
}
