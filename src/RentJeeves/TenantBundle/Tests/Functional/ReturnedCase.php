<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class ReturnedCase extends BaseTestCase
{
    /**
     * @test
     */
    public function removeTenant()
    {
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('tabs.settings');
        $this->page->clickLink('settings.remove');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->fillForm(
            $form,
            array(
                'remove_password'              => "pass",
            )
        );
        $form->pressButton('common.remove');
    }

    /**
     * @test
     * @depends removeTenant
     */
    public function returnedTenant()
    {
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_returnedtype'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_returnedtype_phone'                      => '123123123',
                'rentjeeves_publicbundle_returnedtype_tos'                       => true,
            )
        );
        $form->pressButton('continue');
        $this->page->clickLink('tabs.settings');
        $this->logout();
    }
}
