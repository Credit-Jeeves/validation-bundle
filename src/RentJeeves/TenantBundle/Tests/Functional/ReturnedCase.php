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
        $this->setDefaultSession('Symfony');
        $this->login('tenant11@example.com', 'pass');
        $this->page->clickLink('common.account');
        $this->page->clickLink('settings.remove');
        $this->assertNotNull($form = $this->page->find('css', 'form'));
        $this->fillForm(
            $form,
            array(
                'remove_password'              => "pass",
            )
        );
        $form->pressButton('common.remove');
        $this->assertNotNull($messageBody = $this->page->find('css', '.message-body'));
        $this->assertEquals('authorization.description.removed', $messageBody->getText());
    }

    /**
     * @test
     * @depends removeTenant
     */
    public function returnedTenant()
    {
        $this->setDefaultSession('Symfony');
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($contracts = $this->page->findAll('css', '.contracts'));
        /*We don't have this form any more, because we don't remove user. RT-266
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_returnedtype'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_returnedtype_phone'                      => '123123123',
                'rentjeeves_publicbundle_returnedtype_tos'                       => true,
            )
        );
        $form->pressButton('continue');
        $this->page->clickLink('common.account');*/
        $this->logout();
    }
}
