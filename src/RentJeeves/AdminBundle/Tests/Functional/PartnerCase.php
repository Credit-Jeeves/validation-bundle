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
        $this->loginByAccessToken('admin@creditjeeves.com', $this->getUrl() . 'admin/partner/list');
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
        $this->loginByAccessToken('admin@creditjeeves.com', $this->getUrl() . 'admin/partner/list');
        $this->assertNotNull($this->page->find('css', 'table'));
        $this->page->clickLink('admin.list.partner_users');
        $this->assertNotNull($users = $this->page->findAll('css', 'table tbody tr'));
        $this->assertCount(1, $users);

        $this->page->clickLink('link_action_create');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_return_to_list'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'Jane',
                $fields[1]->getAttribute('id') => 'Green',
                $fields[2]->getAttribute('id') => 'jane@test.com',
                $fields[3]->getAttribute('id') => 'test',
                $fields[4]->getAttribute('id') => 'test'
            )
        );
        $submit->click();
        $this->assertNotNull($users = $this->page->findAll('css', 'table tbody tr'));
        $this->assertCount(2, $users);
    }
}
