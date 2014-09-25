<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use Behat\Mink\Element\NodeElement;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class SettingsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function edit()
    {
        $this->load(true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#block_settings'));
        $tableBlock->clickLink('action_edit');
        $this->assertNotNull($tenants = $this->page->findAll('css', 'a.action_edit'));
        $this->assertNotNull($tabs = $this->page->findAll('css', 'form .tab-pane'));
        $this->assertCount(2, $tabs);
        $form = $tabs[0];
        $this->assertNotNull($fields = $form->findAll('css', 'input'));
        $this->assertCount(4, $fields, 'wrong number of inputs');
        /** @var NodeElement $field */
        foreach ($fields as $field) {
            $this->assertNotNull($field->getValue());
        }

        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'test',
                $fields[1]->getAttribute('id') => 'test',
                $fields[2]->getAttribute('id') => 'test',
                $fields[3]->getAttribute('id') => 'test',
            )
        );
        $this->page->pressButton('btn_update_and_edit_again');
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'), $this->page->getHtml());
        $this->assertEquals('flash_edit_success', $message->getText());
        $this->page->clickLink('Dashboard');
        $tableBlock->clickLink('action_edit');
        /** @var NodeElement $field */
        foreach ($fields as $field) {
            $this->assertEquals('test', $field->getValue());
        }
        $this->logout();
    }
}
