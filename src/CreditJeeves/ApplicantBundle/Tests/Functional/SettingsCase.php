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
    );

    protected $password = '123123';

    /**
     * @test
     */
    public function userChangePassword()
    {
        $this->load($this->fixtures, true);
        $this->setDefaultSession('symfony');
        //$this->setDefaultSession('selenium2');
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
//        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', $this->password);
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.email');

        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));

        $this->fillForm(
            $form,
            array(
                'notification_score_changed_notification' => false,
                'notification_offer_notification' => true,
            )
        );

        $form->pressButton('common.save');
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('Information has been updated', $notice->getText(), 'Wrong notice');

        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.email');

        $this->assertNotNull($notifications = $form->find('css', '#notification_score_changed_notification'));
        $this->assertFalse($notifications->isChecked());
        $this->assertNotNull($offers = $form->find('css', '#notification_offer_notification'));
        $this->assertTrue($offers->isChecked());

        $this->logout();
        
    }

    /**
     * @test
     * @depends userChangePassword
     */
    public function userRemoveData()
    {
//        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', $this->password);
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.remove');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->fillForm(
            $form,
            array(
                'remove_password' => $this->password
            )
        );
        $form->pressButton('common.remove');
    }

    /**
     * @test
     * @depends userChangePassword
     */
    public function userReturned()
    {
//        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', $this->password);
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        //FIXME check errors
//        $form->pressButton('common.get.score');
//        $this->assertCount(2, $this->page->findAll('css', '234'));


        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_leadreturnedtype_code' => 'DVRWP2NFQ6',
                'creditjeeves_applicantbundle_leadreturnedtype_user_street_address1' => 'SAINT NAZAIRE 2010',
                'creditjeeves_applicantbundle_leadreturnedtype_user_unit_no' => '116TH 1',
                'creditjeeves_applicantbundle_leadreturnedtype_user_city' => 'HOMESTEAD',
                'creditjeeves_applicantbundle_leadreturnedtype_user_state' => 'FL',
                'creditjeeves_applicantbundle_leadreturnedtype_user_zip' => '33039',
                'creditjeeves_applicantbundle_leadreturnedtype_user_phone' => '7188491319',
                'creditjeeves_applicantbundle_leadreturnedtype_user_date_of_birth_day' => '19',
                'creditjeeves_applicantbundle_leadreturnedtype_user_date_of_birth_month' => 'Feb',
                'creditjeeves_applicantbundle_leadreturnedtype_user_date_of_birth_year' => '1957',
                'creditjeeves_applicantbundle_leadreturnedtype_user_ssn_ssn1' => '666',
                'creditjeeves_applicantbundle_leadreturnedtype_user_ssn_ssn2' => '81',
                'creditjeeves_applicantbundle_leadreturnedtype_user_ssn_ssn3' => '0987',
                'creditjeeves_applicantbundle_leadreturnedtype_user_tos' => true,
            )
        );
        $form->pressButton('common.get.score');
        $this->assertNotNull($loading = $this->page->find('css', '.loading h4'));
        $this->assertEquals('common.loading.text', $loading->getText());
        $this->logout();
    }
}
