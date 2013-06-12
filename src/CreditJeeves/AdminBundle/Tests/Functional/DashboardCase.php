<?php
namespace CreditJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class DashboardCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
{
    protected $fixtures = array(
        '001_cj_account_group.yml',
        '002_cj_admin_account.yml',
        '003_cj_dealer_account.yml',
        '004_cj_applicant.yml',
        '005_cj_lead.yml',
        '006_cj_applicant_report.yml',
        '007_cj_applicant_score.yml',
        '010_cj_affiliate.yml',
        '013_cj_holding_account.yml',
        '020_email.yml',
        '021_email_translations.yml',
    );

    /**
     * @test
     */
    public function adminManageEmails()
    {
        $this->load($this->fixtures, true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tables = $this->page->findAll('css', '.cms-block table'));
        $this->assertCount(3, $tables, 'Wrong number of blocks');
        $this->assertNotNull($list = $this->page->findAll('css', 'a i.icon-list'));
        $this->assertCount(9, $list, 'Wrong number of blocks');
        $link = $list[0]->getParent();
        $link->click();
        $this->assertNotNull($emails = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(10, $emails);
        $this->page->clickLink('link_action_create');
        $this->assertNotNull($tabs = $this->page->findAll('css', 'form ul li a'));
        $this->assertCount(3, $tabs, 'wrong number of tabs');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_edit_again'));
        $submit->click();
        $this->assertNotNull($error = $this->page->find('css', '.alert-error'));
        $this->assertEquals('flash_create_error', $error->getText());
        $this->assertNotNull($inputs = $this->page->findAll('css', 'form input[type="text"]'));
        $this->assertCount(2, $inputs);
        $id = $inputs[0]->getAttribute('id');
        $this->fillForm(
            $form,
            array(
                $id => 'test',
            )
        );
        $id = $inputs[1]->getAttribute('id');
        $this->fillForm(
            $form,
            array(
                $id => 'test',
            )
        );
        $this->assertNotNull($body = $this->page->findAll('css', 'form textarea'));
        $this->assertCount(1, $body);
        $id = $body[0]->getAttribute('id');
        $this->fillForm(
            $form,
            array(
                $id => 'test',
            )
        );
        $submit->click();
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'));
        $this->assertEquals('flash_create_success', $message->getText());
        $this->page->clickLink('Email Template List');
        $this->assertNotNull($rows = $this->page->findAll('css', 'form table tbody tr'));
        $this->assertCount(11, $rows);
        $this->assertNotNull($items = $rows[10]->findAll('css', 'td'));
        $this->assertEquals('test', $items[1]->getText());
        $this->assertNotNull($delete = $this->page->findAll('css', 'a.delete_link'));
        $this->assertCount(11, $delete);
        $delete[10]->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($delete = $form->findButton('btn_delete'));
        $delete->click();
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'));
        $this->assertEquals('flash_delete_success', $message->getText());
        $this->assertNotNull($rows = $this->page->findAll('css', 'form table tbody tr'));
        $this->assertCount(10, $rows);
        $this->logout();
    }

    /**
     * @test
     */
    public function adminManageAdmins()
    {
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tables = $this->page->findAll('css', '.cms-block table'));
        $this->assertCount(3, $tables, 'Wrong number of blocks');
        $this->assertNotNull($list = $this->page->findAll('css', 'a i.icon-list'));
        $this->assertCount(9, $list, 'Wrong number of blocks');
        $link = $list[1]->getParent();
        $link->click();
        $this->assertNotNull($admins = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(1, $admins);
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
                $fields[3]->getAttribute('id') => 'test@admin.com',
            )
        );
        $submit->click();
        $this->page->clickLink('Admin List');
        $this->assertNotNull($admins = $this->page->findAll('css', 'a.delete_link'));
        $this->assertCount(2, $admins);
        $admins[1]->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($delete = $form->findButton('btn_delete'));
        $delete->click();
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'));
        $this->assertEquals('flash_delete_success', $message->getText());
        $this->assertNotNull($admins = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(1, $admins);
        $this->logout();
    }
}
