<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class GroupCase extends BaseTestCase
{

    /**
     * @test
     */
    public function settingFirst()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($edit = $this->page->findAll('css', 'a.edit_link'));
        $edit[4]->click();

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click();

        $this->assertNotNull($checkbox = $this->page->findAll('css', 'input[type=checkbox]'));
        $this->assertCount(6, $checkbox);
        $checkbox[5]->check(); //Check pay balance only
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNotNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
        $this->assertEquals('pay.balance.only.error', $error->getText());

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click();

        $this->assertNotNull($checkbox = $this->page->findAll('css', 'input[type=checkbox]'));
        $this->assertCount(6, $checkbox);
        $checkbox[3]->check();  //Check is integrated
        $checkbox[5]->check(); //Check pay balance only

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
    }

    /**
     * @test
     */
    public function settingSecond()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($edit = $this->page->findAll('css', 'a.edit_link'));
        $edit[8]->click();

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click();

        $this->assertNotNull($checkbox = $this->page->findAll('css', 'input[type=checkbox]'));
        $this->assertCount(9, $checkbox); // TODO check only current tab
        $checkbox[6]->check();  //Check is integrated
        $checkbox[8]->check(); //Check pay balance only

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNotNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
        $this->assertEquals('pay.balance.only.reccuring_error', $error->getText());
    }
}
