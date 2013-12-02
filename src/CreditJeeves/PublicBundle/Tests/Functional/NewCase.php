<?php
namespace CreditJeeves\PublicBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class NewCase extends BaseTestCase
{

    /**
     * @test
     */
    public function userNewForm()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->session->visit($this->getUrl() . 'new');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $form->pressButton('common.get.score');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list li'));
        $this->assertCount(11, $errors, 'Wrong number of errors');
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_leadnewtype_code' => 'DVRWP2NFQ6',
                'creditjeeves_applicantbundle_leadnewtype_user_first_name' => 'ANGELA',
                'creditjeeves_applicantbundle_leadnewtype_user_middle_initial' => 'LEE',
                'creditjeeves_applicantbundle_leadnewtype_user_last_name' => 'PARKER',
                'creditjeeves_applicantbundle_leadnewtype_user_email' => 'angela@example.com',
                'creditjeeves_applicantbundle_leadnewtype_user_password_Password' => 'pass',
                'creditjeeves_applicantbundle_leadnewtype_user_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_leadnewtype_user_ssn_ssn1' => '666',
                'creditjeeves_applicantbundle_leadnewtype_user_ssn_ssn2' => '36',
                'creditjeeves_applicantbundle_leadnewtype_user_ssn_ssn3' => '6977',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_street' => 'USS SIERRA AD-18',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_unit' => 'S-1',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_city' => 'FPO',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_area' => 'AL',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_zip' => '34084',
                'creditjeeves_applicantbundle_leadnewtype_user_phone' => '3029349291',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_day' => '26',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_month' => '02',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_year' => '1958',
                'creditjeeves_applicantbundle_leadnewtype_target_name_make'        => 'Acura',
                'creditjeeves_applicantbundle_leadnewtype_target_name_model'        => 'ILX',
            )
        );
        $form->pressButton('common.get.score');
        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list li'));
        $this->assertCount(1, $errors, 'Wrong number of errors');
        $this->assertEquals('error.user.tos', $errors[0]->getText(), 'Wrong error');
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_leadnewtype_user_password_Password' => 'pass',
                'creditjeeves_applicantbundle_leadnewtype_user_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_leadnewtype_user_tos' => true
            )
        );
        $form->pressButton('common.get.score');
        $this->assertNotNull($title = $this->page->find('css', 'h1'));
        $this->assertEquals('user.email.verify', $title->getText(), 'Wrong score');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->assertNotNull($submit = $form->findButton('user.email.again'));
        $submit->click();
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(2, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->assertNotNull($subject = $this->page->find('css', '#subject span'));
        $this->assertEquals('Check Email', $subject->getText());
        $this->assertNotNull($body = $this->page->find('css', '#body'));
        $this->assertNotNull($htmlLink = $this->page->find('css', 'a'));
        $htmlLink->click();
        $this->assertNotNull($body = $this->page->find('css', '#email-body'));
        $this->assertNotEquals(false, $url = $this->retrieveAbsoluteUrl($body->getText()));
        $this->session->visit($url);
        $this->login('angela@example.com', 'pass');
        $this->logout();
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(3, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->assertNotNull($subject = $this->page->find('css', '#subject span'));
        $this->assertEquals('Welcome to Credit Jeeves', $subject->getText());
        $this->assertNotNull($body = $this->page->find('css', '#body'));
        $this->assertNotNull($htmlLink = $this->page->find('css', 'a'));
        $htmlLink->click();
        $this->assertNotNull($title = $this->page->find('css', 'h1'));
        $this->assertEquals('Welcome to CreditJeeves', $title->getText());
    }

    /**
     * @test
     * @depends userNewForm
     */
    public function newEmilioLeadVehicle()
    {
        $this->load(true);
        $this->session->visit($this->getUrl() . 'new/dealer/GENERIC');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_leadnewtype_user_first_name' => 'BRIAN',
                'creditjeeves_applicantbundle_leadnewtype_user_middle_initial' => 'P',
                'creditjeeves_applicantbundle_leadnewtype_user_last_name' => 'KURTH',
                'creditjeeves_applicantbundle_leadnewtype_user_email' => 'emilio@example.com',
                'creditjeeves_applicantbundle_leadnewtype_user_password_Password' => 'pass',
                'creditjeeves_applicantbundle_leadnewtype_user_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_leadnewtype_user_ssn_ssn1' => '666',
                'creditjeeves_applicantbundle_leadnewtype_user_ssn_ssn2' => '81',
                'creditjeeves_applicantbundle_leadnewtype_user_ssn_ssn3' => '0987',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_street' => '2010 SAINT NAZAIRE BLVD',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_unit' => 'S-1',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_city' => 'HOMESTEAD',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_area' => 'FL',
                'creditjeeves_applicantbundle_leadnewtype_user_addresses_0_zip' => '33039',
                'creditjeeves_applicantbundle_leadnewtype_user_phone' => '3013246413',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_day' => '01',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_month' => '01',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_year' => '1963',
                'creditjeeves_applicantbundle_leadnewtype_user_tos' => true
            )
        );
        $form->pressButton('common.get.score');
        $this->assertNotNull($title = $this->page->find('css', 'h1'));
        $this->setDefaultSession('selenium2');
        $this->login('emilio@example.com', 'pass');
        $this->assertNotNull($select = $this->page->find('css', '#lead-select-button'));
        $select->click();
        $this->session->wait(
            $this->timeout + 3000,
            "jQuery('#lead-select-form .lead-select-lead').length > 0"
        );
        $this->assertNotNull($form = $this->page->find('css', '#lead-select-form'));
        $this->assertNotNull($links = $this->page->findAll('css', '.lead-select-lead'));
        $this->assertCount(4, $links, 'Wrong number of accounts');
        
        $this->logout();
        
    }
}
