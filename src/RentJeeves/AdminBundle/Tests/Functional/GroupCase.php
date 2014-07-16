<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class GroupCase extends BaseTestCase
{

    /**
     * @test
     */
    public function adminManageLandlords()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($edit = $this->page->findAll('css', 'a.edit_link'));
        $edit[4]->click();

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click();

        $this->assertNotNull($checkbox = $this->page->findAll('css', 'input[type=checkbox]'));
        $this->assertCount(3, $checkbox);
        $checkbox[2]->check(); //Check pay balance only
        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNotNull($menu = $this->page->findAll('css', '.nav-tabs li>a'));
        $menu[4]->click();

        $this->assertNotNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
        $this->assertEquals('is.pay.balance.only.error', $error->getText());

        $this->assertNotNull($checkbox = $this->page->findAll('css', 'input[type=checkbox]'));
        $this->assertCount(3, $checkbox);
        $checkbox[1]->check();  //Check is integrated
        $checkbox[2]->check(); //Check pay balance only

        $this->assertNotNull($submit = $this->page->find('css', '.btn-primary'));
        $submit->click();

        $this->assertNull($error = $this->page->find('css', '.sonata-ba-form-error li'));
        $this->logout();
    }
}
