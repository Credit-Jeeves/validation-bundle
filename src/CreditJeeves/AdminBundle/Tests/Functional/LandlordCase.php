<?php
namespace CreditJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class LandlordCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
{

    /**
     * @test
     */
    public function adminManageLandlords()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_landlords'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(7, $landlords);
        $this->page->clickLink('link_action_create');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_edit_again'));
        $submit->click();
        $this->assertNotNull($error = $this->page->find('css', '.alert-error'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->assertCount(13, $fields, 'wrong number of inputs');
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'test',
                $fields[2]->getAttribute('id') => 'test',
                $fields[3]->getAttribute('id') => 'test@landlord.com',
                $fields[5]->getAttribute('id') => 'pass',
                $fields[6]->getAttribute('id') => 'pass',
            )
        );
        $submit->click();
        $this->page->clickLink('Landlord List');
        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.delete_link'));
        $this->assertCount(8, $landlords);
        $landlords[1]->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($delete = $form->findButton('btn_delete'));
        $delete->click();
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'));
        $this->assertEquals('flash_delete_success', $message->getText());
        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(7, $landlords);
        $this->logout();
    }

    /**
     * @test
     */
    public function observeLandlord()
    {
        $this->load(false);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_landlords'));
        $tableBlock->clickLink('link_list');
        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.observe_link'));
        $this->assertCount(7, $landlords);
        $landlords[0]->click();
        $this->page->clickLink('tabs.tenants');
        $this->page->clickLink('tabs.properties');
        $this->page->clickLink('Back to Admin');
        $this->logout();
    }
}
