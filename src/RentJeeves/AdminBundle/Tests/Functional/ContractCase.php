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
        $this->loginByAccessToken('admin@creditjeeves.com', $this->getUrl() . 'admin/tenant/list');
        $this->assertNotNull($contractList = $this->page->find('css', '.contract_link'));
        $contractList->click();
        $this->assertNotNull($editContract = $this->page->find('css', 'i.icon-edit'));
        $editContract->getParent()->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->find('css', 'input.btn-primary'));
        $this->assertCount(7, $fields = $this->page->findAll('css', 'form input'));
        $this->assertCount(23, $fieldsSelected = $this->page->findAll('css', 'form select'));
        $statusTo = $fieldsSelected[5]->getValue();
        $search = $fields[0]->getValue();
        $this->assertFalse($statusTo == 'invite');
        $this->assertFalse($search == 10);
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 10,
                $fieldsSelected[5]->getAttribute('id') => 'invite'
            )
        );
        $submit->click();

        $statusAfter = $fieldsSelected[5]->getValue();
        $searchAfter = $fields[0]->getValue();
        $this->assertTrue($statusAfter == 'invite');
        $this->assertTrue($searchAfter == 10);
        $this->logout();
    }
}
