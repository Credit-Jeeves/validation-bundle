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

    /**
     * @test
     * @depends filter
     */
    public function butchRun()
    {
        $this->setDefaultSession('symfony');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($block = $this->page->find('css', '#id_block_paymnets'));
        $block->clickLink('link_list');

        $this->assertNotNull($table = $this->page->find('css', 'table'));
        $this->assertNotNull($checkBoxes = $table->findAll('css', '.sonata-ba-list-field input'));
        $this->assertCount(2, $checkBoxes);

        foreach ($checkBoxes as $checkBox) {
            $checkBox->check();
        }
        $this->page->pressButton('btn_batch');
        $this->page->pressButton('btn_execute_batch_action');

        $this->assertNotNull($alert = $this->page->find('css', '.alert-success'));
        $this->assertEquals('admin.butch.run.success-1', $alert->getText());

        foreach ($checkBoxes as $checkBox) {
            $checkBox->check();
        }
        $this->page->pressButton('btn_batch');
        $this->page->pressButton('btn_execute_batch_action');

        $this->assertNotNull($alert = $this->page->find('css', '.alert-warning'));
        $this->assertEquals('admin.butch.run.warning', $alert->getText());

    }
}
