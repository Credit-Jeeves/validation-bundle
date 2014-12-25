<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ContractCase extends BaseTestCase
{
    /**
     * @test
     */
    public function editContract()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_tenants'));
        $tableBlock->clickLink('link_list');
        $this->assertNotNull($contractList = $this->page->find('css', '.contract_link'));
        $contractList->click();
        $this->assertNotNull($editContract = $this->page->find('css', 'i.icon-edit'));
        $editContract->getParent()->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->find('css', 'input.btn-primary'));
        $this->assertCount(6, $fields = $this->page->findAll('css', 'form input'));
        $this->assertCount(22, $fieldsSelected = $this->page->findAll('css', 'form select'));
        $statusTo = $fieldsSelected[5]->getValue();
        $dueDateTo = $fields[1]->getValue();
        $this->assertFalse($statusTo == 'invite');
        $this->assertFalse($dueDateTo == 10);
        $this->fillForm(
            $form,
            array(
                $fields[1]->getAttribute('id') => 10,
                $fieldsSelected[5]->getAttribute('id') => 'invite'
            )
        );
        $submit->click();
        $statusAfter = $fieldsSelected[5]->getValue();
        $dueDateAfter = $fields[1]->getValue();
        $this->assertTrue($statusAfter == 'invite');
        $this->assertTrue($dueDateAfter == 10);
        $this->logout();
    }
}
