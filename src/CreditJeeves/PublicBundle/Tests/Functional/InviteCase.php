<?php
namespace CreditJeeves\PublicBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class InviteCase extends BaseTestCase
{
    /**
     * @test
     */
    public function userInvite()
    {
        $this->setDefaultSession('symfony');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'invite/TESTCODE');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $form->pressButton('common.i_agree');

        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list li'));
        $this->assertCount(2, $errors, 'Wrong number of errors');

        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_newpasswordtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_newpasswordtype_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_newpasswordtype_date_of_birth_day' => '01', //'01',
                'creditjeeves_applicantbundle_newpasswordtype_date_of_birth_month' => '01', //'01',
                'creditjeeves_applicantbundle_newpasswordtype_date_of_birth_year' => '1937',
                'creditjeeves_applicantbundle_newpasswordtype_tos' => true,
            )
        );
        $form->pressButton('common.i_agree');
        $this->login('app14@example.com', 'pass');
        $this->logout();
    }

    /**
     * @test
     */
    public function userInviteFull()
    {
        $this->load(false);
        $this->session->visit($this->getUrl() . 'invite/TESTFULL');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $form->pressButton('common.i_agree');

        $this->assertNotNull($errors = $this->page->findAll('css', '.error_list li'));
        $this->assertCount(9, $errors, 'Wrong number of errors');

        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_usernewtype_first_name' => 'LINDA',
                'creditjeeves_applicantbundle_usernewtype_middle_initial' => 'A',
                'creditjeeves_applicantbundle_usernewtype_last_name' => 'LEMOINE',
                'creditjeeves_applicantbundle_usernewtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_password_Password' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_password_Retype' => 'pass',
                'creditjeeves_applicantbundle_usernewtype_ssn_ssn1' => '666',
                'creditjeeves_applicantbundle_usernewtype_ssn_ssn2' => '36',
                'creditjeeves_applicantbundle_usernewtype_ssn_ssn3' => '6977',
                'creditjeeves_applicantbundle_usernewtype_addresses_0_street' => 'USS SIERRA AD-18',
                'creditjeeves_applicantbundle_usernewtype_addresses_0_unit' => 'S-1',
                'creditjeeves_applicantbundle_usernewtype_addresses_0_city' => 'FPO',
                'creditjeeves_applicantbundle_usernewtype_addresses_0_area' => 'AL',
                'creditjeeves_applicantbundle_usernewtype_addresses_0_zip' => '34084',
                'creditjeeves_applicantbundle_usernewtype_phone' => '8189976080',
                'creditjeeves_applicantbundle_usernewtype_date_of_birth_day' => '01', //'01',
                'creditjeeves_applicantbundle_usernewtype_date_of_birth_month' => '01', // '01',
                'creditjeeves_applicantbundle_usernewtype_date_of_birth_year' => '1940',
                'creditjeeves_applicantbundle_usernewtype_tos' => true,
            )
        );

        $form->pressButton('common.i_agree');
        //$this->login('linda@example.com', 'pass');
        $this->logout();
    }

    /**
     * @test
     */
    public function userInviteFullCarTest()
    {
        $this->setDefaultSession('selenium2');
        $this->load(false);
        $this->session->visit($this->getUrl() . 'invite/TESTFULL');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        //Select Audi
        $this->assertNotNull(
            $make = $this->page->find('css', '#creditjeeves_applicantbundle_usernewtype_target_name_make_link')
        );
        $make->click();
        $this->assertNotNull(
            $make = $this->page->find('css', '#creditjeeves_applicantbundle_usernewtype_target_name_make_li_2')
        );
        $make->click();
        //End select Audi
        //Select Q7
        $this->assertNotNull(
            $make = $this->page->find('css', '#creditjeeves_applicantbundle_usernewtype_target_name_model_link')
        );
        $make->click();
        $this->assertNotNull(
            $make = $this->page->find('css', '#creditjeeves_applicantbundle_usernewtype_target_name_model_li_13')
        );
        $make->click();
        //End select Q7

        $form->pressButton('common.i_agree');
        $this->assertNotNull(
            $selected = $this->page->findAll('css', '.linkselect-link-text')
        );

        $this->assertEquals(7, count($selected));
        $this->assertEquals('Audi', $selected[5]->getText());
        $this->assertEquals('Q7', $selected[6]->getText());
    }
}
