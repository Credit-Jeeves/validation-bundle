<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class DepositNotifyCase extends BaseTestCase
{
    /**
     *
     * It's test for src/RentJeeves/DataBundle/EventListener/DepositAccountListener.php
     *
     * @test
     */
    public function testNotify()
    {
        $this->clearEmail();
        $this->load(true);
        $this->setDefaultSession('symfony');
        //$this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_groups'));
        $tableBlock->clickLink('link_list');

        $this->assertNotNull($group = $this->page->findAll('css', 'a.edit_link'));
        $group[12]->click();

        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->assertCount(8, $fields, 'wrong number of inputs');
        $this->fillForm(
            $form,
            array(
                $fields[1]->getAttribute('id') => 'test',
            )
        );
        $this->assertNotNull($submit = $form->findButton('btn_update_and_edit_again'));
        $submit->click();
        $this->assertNotNull($submit = $form->findButton('btn_update_and_edit_again'));
        $submit->click();

        //Check email notify landlord about added merchant Id
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
    }
}
