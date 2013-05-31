<?php
namespace CreditJeeves\PublicBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class InviteCase extends BaseTestCase
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
    public function userInvite()
    {
        $this->load($this->fixtures, true);
        $this->setDefaultSession('selenium2');
        $this->session->visit($this->getUrl() . 'invite/TESTCODE');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.i_agree'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_newpasswordtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_newpasswordtype_password_Retype' => 'pass',
            )
        );
        $this->assertNotNull($check = $this->page->findAll('css', 'form .checkbox-off'));
        $this->assertCount(1, $check, 'Wrong number of checkboxes');
        $check[0]->click();
        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('form .checkbox-on').length > 0"
        );
        $submit->click();
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.i_agree'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_newpasswordtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_newpasswordtype_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_newpasswordtype_date_of_birth_day' => '1', //'01',
                'creditjeeves_applicantbundle_newpasswordtype_date_of_birth_month' => 'Jan', //'01',
                'creditjeeves_applicantbundle_newpasswordtype_date_of_birth_year' => '1937',
            )
        );
        $submit->click();
        $this->login('app14@example.com', 'pass');
        $this->logout();
    }

    /**
     * @test
     * @depends userInvite
     */
    public function userInviteFull()
    {
        $this->session->visit($this->getUrl() . 'invite/TESTFULL');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.i_agree'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_usernewtype_first_name' => 'LINDA',
                'creditjeeves_applicantbundle_usernewtype_middle_initial' => 'A',
                'creditjeeves_applicantbundle_usernewtype_last_name' => 'LEMOINE',
                'creditjeeves_applicantbundle_usernewtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_street_address1' => '7635 LANKERSHIM BLVD',
                'creditjeeves_applicantbundle_usernewtype_unit_no' => 'APT 16',
                'creditjeeves_applicantbundle_usernewtype_city' => 'NORTH HOLLYWOOD',
                'creditjeeves_applicantbundle_usernewtype_state' => 'CA',
                'creditjeeves_applicantbundle_usernewtype_zip' => '91605',
                'creditjeeves_applicantbundle_usernewtype_phone' => '8189976080',
                'creditjeeves_applicantbundle_usernewtype_date_of_birth_day' => '1', //'01',
                'creditjeeves_applicantbundle_usernewtype_date_of_birth_month' => 'Jan', // '01',
                'creditjeeves_applicantbundle_usernewtype_date_of_birth_year' => '1940',
            )
        );
        $this->assertNotNull(
            $ssn1 = $this->page->find(
                'css',
                '#ssn_creditjeeves_applicantbundle_usernewtype_ssn_ssn1'
            )
        );
        $ssn1->click();
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_usernewtype_ssn_ssn1' => '666',
            )
        );
        $submit->click();
        $this->assertNotNull(
            $ssn2 = $this->page->find(
                'css',
                '#ssn_creditjeeves_applicantbundle_usernewtype_ssn_ssn2'
            )
        );
        $ssn2->click();
        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('#ssn_creditjeeves_applicantbundle_usernewtype_ssn_ssn2').css('display') == 'none'"
        );
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_usernewtype_ssn_ssn2' => '36',
            )
        );
        $submit->click();
        $this->assertNotNull(
            $ssn3 = $this->page->find(
                'css',
                '#ssn_creditjeeves_applicantbundle_usernewtype_ssn_ssn3'
            )
        );
        $ssn3->click();
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_usernewtype_ssn_ssn3' => '6977',
            )
        );
        $submit->click();
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_usernewtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_password_Retype' => 'pass',
            )
        );
        $this->assertNotNull($check = $this->page->findAll('css', 'form .checkbox-off'));
        $this->assertCount(1, $check, 'Wrong number of checkboxes');
        $check[0]->click();
        $this->session->wait(
            $this->timeout + 10000,
            "jQuery('form .checkbox-on').length > 0"
        );
        $submit->click();
        $this->login('linda@example.com', 'pass');
        $this->logout();
    }
}
