<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class PartnerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function adminManagePartner()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_partners'));
        $tableTr->clickLink('link_list');
        $this->assertNotNull($partner = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(1, $partner);
        $this->assertNotNull($this->page->find('css', 'table.table-bordered'));
        $this->page->clickLink('link_action_create');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_return_to_list'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->assertNotNull($fieldsSelected = $this->page->findAll('css', 'form select'));
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'test',
                $fields[1]->getAttribute('id') => 'test',
                $fieldsSelected[0]->getAttribute('id') => 'TestApp'
            )
        );
        $submit->click();
        $this->assertNotNull($partner = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(2, $partner);
        $this->logout();
    }

    /**
     * @test
     */
    public function adminManagePartnerUser()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_partners'));
        $tableTr->clickLink('link_list');
        $this->assertNotNull($this->page->find('css', 'table'));
        $this->page->clickLink('admin.list.partner_users');
        $this->assertNull($this->page->find('css', 'table'));
        $this->page->clickLink('link_action_create');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_return_to_list'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'test',
                $fields[1]->getAttribute('id') => 'test',
                $fields[2]->getAttribute('id') => 'test@test.com',
                $fields[3]->getAttribute('id') => 'test',
                $fields[4]->getAttribute('id') => 'test'
            )
        );
        $submit->click();
        $this->assertNotNull($this->page->find('css', 'table'));
        $this->logout();
    }
}
