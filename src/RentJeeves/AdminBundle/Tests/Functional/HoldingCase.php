<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class HoldingCase extends BaseTestCase
{

    /**
     * @test
     */
    public function create()
    {
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_holdings'));
        $tableBlock->clickLink('link_add');
        $this->assertNotNull($textFields = $this->page->findAll('css', 'input[type=text]'));
        $this->assertCount(8, $textFields);
        $textFields[0]->setValue('Test');
        $textFields[1]->setValue('https://www.yardiaspca12.com/53467milhouse/');
        $textFields[2]->setValue('Test');
        $textFields[3]->setValue('Test');
        $textFields[4]->setValue('Test');
        $textFields[5]->setValue('Test');
        $textFields[6]->setValue('Test');
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();
        $this->assertNotNull($this->page->find('css', '.alert-success'));
        $this->logout();
    }
}
