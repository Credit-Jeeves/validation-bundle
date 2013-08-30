<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class TenantCase extends BaseTestCase
{
    /**
     * @test
     */
    public function adminManageTenants()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_tenants'));
        $tableBlock->clickLink('link_list');
        $this->assertNotNull($tenants = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(21, $tenants);
        $this->page->clickLink('link_action_create');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_edit_again'));
        $submit->click();
        $this->assertNotNull($submit = $form->findButton('btn_create_and_return_to_list'));
        $this->assertNotNull($error = $this->page->find('css', '.alert-error'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->assertCount(12, $fields, 'wrong number of inputs');
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'test',
                $fields[2]->getAttribute('id') => 'test',
                $fields[3]->getAttribute('id') => 'test_new@tenant.com',
                $fields[5]->getAttribute('id') => 'pass',
                $fields[6]->getAttribute('id') => 'pass',
            )
        );
        $submit->click();
        $this->assertNotNull($tenants = $this->page->findAll('css', 'a.delete_link'));
        $this->assertCount(22, $tenants);
        $tenants[1]->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($delete = $form->findButton('btn_delete'));
        $delete->click();
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'));
        $this->assertEquals('flash_delete_success', $message->getText());
        $this->assertNotNull($tenants = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(21, $tenants);
        $this->logout();
    }

    /**
     * @test
     */
    public function observeTenant()
    {
        $this->load(false);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_tenants'));
        $tableBlock->clickLink('link_list');
        $this->assertNotNull($tenants = $this->page->findAll('css', 'a.observe_link'));
        $this->assertCount(21, $tenants);
        sleep(3);
        $tenants[0]->click();
        sleep(10);
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('back.to.admin');
        $this->logout();
    }
}
