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
        $this->assertCount(10, $errors, 'Wrong number of errors');
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
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_month' => 'Feb',
                'creditjeeves_applicantbundle_leadnewtype_user_date_of_birth_year' => '1958',
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
}
