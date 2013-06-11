<?php
namespace CreditJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class LandlordCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
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
    public function adminManageLandlords()
    {
        $this->load($this->fixtures, true);
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_landlords'));

        $tableBlock->clickLink('link_list');


        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(1, $landlords);
        $this->page->clickLink('link_action_create');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('btn_create_and_edit_again'));
        $submit->click();
        $this->assertNotNull($error = $this->page->find('css', '.alert-error'));
        $this->assertNotNull($fields = $this->page->findAll('css', 'form input'));
        $this->assertCount(12, $fields, 'wrong number of inputs');
        $this->fillForm(
            $form,
            array(
                $fields[0]->getAttribute('id') => 'test',
                $fields[2]->getAttribute('id') => 'test',
                $fields[3]->getAttribute('id') => 'test@landlord.com',
            )
        );
        $submit->click();
        $this->page->clickLink('Landlord List');
        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.delete_link'));
        $this->assertCount(2, $landlords);
        $landlords[1]->click();
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($delete = $form->findButton('btn_delete'));
        $delete->click();
        $this->assertNotNull($message = $this->page->find('css', '.alert-success'));
        $this->assertEquals('flash_delete_success', $message->getText());
        $this->assertNotNull($landlords = $this->page->findAll('css', 'a.edit_link'));
        $this->assertCount(1, $landlords);
        $this->logout();
    }
}
