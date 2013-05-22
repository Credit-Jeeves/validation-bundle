<?php
namespace CreditJeeves\ApplicantBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class SettingsCase extends BaseTestCase
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

    protected $password = '123123';

    /**
     * @test
     */
    public function userChangePassword()
    {
        $this->load($this->fixtures, true);
        //$this->setDefaultSession('selenium2');
        $this->setDefaultSession('goutte');
        $this->login('emilio@example.com', 'pass');
        $this->page->clickLink('tabs.settings');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.update'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_passwordtype_password' => 'pass',
                'creditjeeves_applicantbundle_passwordtype_password_new_Password' => $this->password,
                'creditjeeves_applicantbundle_passwordtype_password_new_Retype' => $this->password,
            )
        );
        $submit->click();
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('Information has been updated', $notice->getText(), 'Wrong notice');
        $this->logout();
    }

    /**
     * @test
     * @depends userChangePassword
     */
    public function userContactInformation()
    {
        //$this->setDefaultSession('goutte');
        //$this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', $this->password);
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.contact_information');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.update'));
        $this->fillForm(
            $form,
            array(
                'contact_phone_type' => 0,
                'contact_phone' => 123456789,
            )
        );
        $submit->click();
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('Information has been updated', $notice->getText(), 'Wrong notice');
        $this->logout();
    }

    /**
     * @test
     * @depends userChangePassword
     */
    public function userEmailSettings()
    {
        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', $this->password);
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.email');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.save'));
        $this->assertNotNull($check = $this->page->findAll('css', '.checkbox-on'));
        $this->assertCount(2, $check, 'Wrong number of checkboxes');
        $check[0]->click();
        $this->session->wait(
                $this->timeout + 10000,
                "jQuery('form .checkbox-off').length > 0"
        );
        $this->assertNotNull($check = $this->page->findAll('css', '.checkbox-on'));
        $this->assertCount(1, $check, 'Wrong number of checkboxes');
        
        $check[0]->click();
        $this->session->wait(
                $this->timeout + 10000,
                "jQuery('form .checkbox-off').length > 1"
        );
        $submit->click();
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('Information has been updated', $notice->getText(), 'Wrong notice');
        $this->logout();
        
    }

    /**
     * @test
     * @depends userChangePassword
     */
    public function userRemoveData()
    {
        $this->setDefaultSession('goutte');
        $this->login('emilio@example.com', $this->password);
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.remove');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.remove'));
        $this->fillForm(
                $form,
                array(
                        'remove_password' => $this->password
                )
        );
        $submit->click();
        $this->login('emilio@example.com', $this->password);
        $this->logout();
    }
}
