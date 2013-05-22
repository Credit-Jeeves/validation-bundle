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
        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', 'pass');
        $this->page->clickLink('tabs.settings');
        $this->assertNotNull($form = $this->page->find('css', '.column-middle form'));
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
        //sleep(5);
        $this->logout();
    }

    /**
     * @test
     * @depends userChangePassword
     */
    public function userCreditSummary()
    {
//         $this->setDefaultSession('goutte');
        $this->login('emilio@example.com', $this->password);
        $this->page->clickLink('tabs.settings');
        $this->logout();
    }
}
