<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class PasswordCase extends BaseTestCase
{
    /**
     * @test
     */
    public function password()
    {
        $this->setDefaultSession('symfony');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('common.account');
        $this->assertNotNull($form = $this->page->find('css', '#resetting_password'));
        $this->fillForm(
            $form,
            array(
                'creditjeeves_applicantbundle_passwordtype_password'              => "pass",
                'creditjeeves_applicantbundle_passwordtype_password_new_Retype'   => "1234",
                'creditjeeves_applicantbundle_passwordtype_password_new_Password' => "1234",
            )
        );
        $form->pressButton('common.update');
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->logout();
        $this->login('landlord1@example.com', '1234');
        $this->page->clickLink('common.account');
        $this->assertNotNull($form = $this->page->find('css', '#resetting_password'));
    }
}
