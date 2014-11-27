<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use Behat\Mink\Element\DocumentElement;
use CreditJeeves\TestBundle\Functional\BaseTestCase;
use CreditJeeves\UserBundle\Tests\Traits\SettingsCaseTrait;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class SettingsCase extends \CreditJeeves\TestBundle\Functional\BaseTestCase
{
//    TODO Torn on it if function would be approved for CJ
//    use SettingsCaseTrait;
//
    protected $password = '123123';
    protected $userEmail = 'emilio@example.com';
//    protected $accountLink = 'tabs.settings';

    /**
     * @test
     */
    public function userChangePassword()
    {
        $this->load(true);
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
                'contact_phone' => 1234567890,
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
}
