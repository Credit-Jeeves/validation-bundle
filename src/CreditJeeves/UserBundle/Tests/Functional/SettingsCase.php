<?php
namespace CreditJeeves\UserBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class SettingsCase extends BaseTestCase
{
    protected $password = '123123';
    protected $userEmail = 'tenant11@example.com';
    protected $accountLink = 'common.account';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->load(true);
    }

    /**
     * @test
     */
    public function userChangePassword()
    {
        $this->setDefaultSession('goutte');
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
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

        $this->login($this->userEmail, $this->password);
        $this->logout();
    }

    /**
     * @test
     */
    public function userContactInformation()
    {
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.contact_information');
        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));
        $this->assertNotNull($submit = $form->findButton('common.update'));
        $this->fillForm(
            $form,
            array(
                'contact_first_name' => 'Tim',
                'contact_last_name' => 'Cook',
                'contact_phone' => 1234567890,
            )
        );
        $submit->click();
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('contact.information.update', $notice->getText(), 'Wrong notice');
        $this->logout();
    }

    /**
     * @test
     */
    public function userEmailSettings()
    {
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.email');

        $this->assertNotNull($form = $this->page->find('css', '.pod-middle form'));

        $this->fillForm(
            $form,
            array(
                'notification_emailNotification' => false,
                'notification_offer_notification' => true,
            )
        );

        $form->pressButton('common.save');
        $this->assertNotNull($notice = $this->page->find('css', '.flash-notice'));
        $this->assertEquals('Information has been updated', $notice->getText(), 'Wrong notice');

        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.email');

        $this->assertNotNull($notifications = $form->find('css', '#notification_emailNotification'));
        $this->assertFalse($notifications->isChecked());
        $this->assertNotNull($offers = $form->find('css', '#notification_offer_notification'));
        $this->assertTrue($offers->isChecked());

        $this->logout();
    }

    /**
     * @test
     */
    public function userAddNewAddressSettings()
    {
        $this->setDefaultSession('goutte');
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.address.head.manage');

        /** @var DocumentElement[] $rows */
        $this->page->clickLink('settings.address.action.add');
        $this->assertNotNull(
            $form = $this->page->find('css', '#creditjeeves_userbundle_useraddresstype'),
            'Cant find form'
        );
        $this->fillForm(
            $form,
            [
                'creditjeeves_userbundle_useraddresstype_street' => '',
            ]
        );

        // Check for validation (submit an empty form)
        $form->pressButton('common.add');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(4, $errorList, 'Wrong number of received errors');

        // Submit a filled form
        $testStreetName = 'NEW_ADDED_STREET';
        $this->fillForm(
            $form,
            [
                'creditjeeves_userbundle_useraddresstype_street' => $testStreetName,
                'creditjeeves_userbundle_useraddresstype_city' => 'BELTSVILLE',
                'creditjeeves_userbundle_useraddresstype_area' => 'MD',
                'creditjeeves_userbundle_useraddresstype_zip' => '207041563',
                'creditjeeves_userbundle_useraddresstype_unit' => '116TH 1'
            ]
        );

        $form->pressButton('common.add');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(2, $errorList, 'Wrong number of received errors');

        $testStreetName = 'NEW ADDED STREET';
        $this->fillForm(
            $form,
            [
                'creditjeeves_userbundle_useraddresstype_street' => $testStreetName,
                'creditjeeves_userbundle_useraddresstype_city' => 'BELTSVILLE',
                'creditjeeves_userbundle_useraddresstype_area' => 'MD',
                'creditjeeves_userbundle_useraddresstype_zip' => '20704',
                'creditjeeves_userbundle_useraddresstype_unit' => '116TH 1'
            ]
        );

        $this->assertNotNull(
            $checkbox = $this->page->find('css', '#creditjeeves_userbundle_useraddresstype_isDefault'),
            'The isDefault checkbox was not found.'
        );
        $checkbox->check();

        $form->pressButton('common.add');

        /** @var DocumentElement[] $rows */
        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.addresses-table tbody tr'),
            'Can not find addresses.'
        );
        // Check that address was added and is default
        $this->assertContains(
            $testStreetName,
            $rows[0]->getHtml(),
            'Can not find the added address on the first position.'
        );
        $this->assertNotNull(
            $rows[0]->find('css', '.default-address'),
            'The added address should have a "(default)" marker.'
        );

        $this->logout();
    }

    /**
     * @test
     */
    public function userEditAddressSettings()
    {
        $this->setDefaultSession('goutte');
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.address.head.manage');

        /** @var DocumentElement[] $rows */
        $this->assertGreaterThanOrEqual(
            2,
            $rows = $this->page->findAll('css', '.addresses-table tbody tr'),
            'This user should have two or more fixtures.'
        );
        $this->assertNotNull($link = $rows[1]->find('css', 'a.type-ico.edit'), 'Can not find link to edit page.');
        $link->click();

        $this->assertNotNull(
            $form = $this->page->find('css', '#creditjeeves_userbundle_useraddresstype'),
            'Cant find form'
        );

        $testStreetName = 'NEW ADDED STREET';
        $this->fillForm(
            $form,
            [
                'creditjeeves_userbundle_useraddresstype_street' => $testStreetName,
            ]
        );

        $this->assertNotNull(
            $checkbox = $this->page->find('css', '#creditjeeves_userbundle_useraddresstype_isDefault'),
            'The isDefault checkbox was not found.'
        );
        $checkbox->check();

        $form->pressButton('common.save');

        $this->assertNotNull(
            $rows = $this->page->findAll('css', '.addresses-table tbody tr'),
            'Can not find addresses.'
        );
        // Check that address was edited and is default
        $this->assertContains(
            $testStreetName,
            $rows[0]->getHtml(),
            'Can not find the edited address on the first position.'
        );
        $this->assertContains(
            'default-address',
            $rows[0]->getHtml(),
            'The edited address should have a "(default)" marker.'
        );

        // Check that all the other addresses does not have a "(default)" marker
        unset($rows[0]);
        foreach ($rows as $row) {
            $this->assertNotContains('default-address', $row->getHtml(), 'User should have only one default address.');
        }

        $this->logout();
    }

    /**
     * @test
     */
    public function shouldShowErrorIfTryDeleteAddressWithActivePaymentAccount()
    {
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository('DataBundle:User')->findOneBy(['email' => $this->userEmail]);
        $this->assertCount(2, $user->getAddresses());

        /** @var Address $notDefaultAddress */
        $notDefaultAddress = $user->getAddresses()->last();
        $this->assertFalse($notDefaultAddress->getIsDefault());
        $this->assertNull($notDefaultAddress->getDeletedAt());

        $this->setDefaultSession('selenium2');

        $this->login($user->getEmail(), 'pass');

        $this->page->clickLink('common.account');
        $this->page->clickLink('settings.address.head.manage');

        $this->assertNotNull(
            $deleteButton = $this->page->find(
                'css',
                'a.delete[href$=' . $notDefaultAddress->getId() . ']'
            )
        );

        $deleteButton->click();

        $this->page->clickLink('settings.address.action.delete.yes');

        $this->assertNotNull($error = $this->page->find('css', 'div.flash-error'));
        $this->assertContains('settings.address.delete.error.has_active_pa', $error->getText());
    }

    /**
     * @test
     */
    public function shouldShowErrorIfTryDeleteDefaultAddress()
    {
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository('DataBundle:User')->findOneBy(['email' => $this->userEmail]);
        $this->assertCount(2, $user->getAddresses());

        /** @var Address $defaultAddress */
        $defaultAddress = $user->getAddresses()->first();
        $this->assertTrue($defaultAddress->getIsDefault());
        $this->assertNull($defaultAddress->getDeletedAt());

        $this->setDefaultSession('symfony');

        $this->login($user->getEmail(), 'pass');

        $this->session->visit($this->getUrl() . 'address-delete/' . $defaultAddress->getId());

        $this->assertNotNull($error = $this->page->find('css', 'div.flash-error'));
        $this->assertContains('settings.address.delete.error.default', $error->getText());
    }

    /**
     * @test
     */
    public function shouldSetDeletedAtIfAddressDoNotHaveActivePaymentAccounts()
    {
        $this->setDefaultSession('selenium2');
        /** @var User $user */
        $user = $this->getEntityManager()->getRepository('DataBundle:User')->findOneBy(['email' => $this->userEmail]);
        $this->assertCount(2, $user->getAddresses());

        /** @var Address $notDefaultAddress */
        $notDefaultAddress = $user->getAddresses()->last();
        $this->assertFalse($notDefaultAddress->getIsDefault());
        $this->assertNull($notDefaultAddress->getDeletedAt());

        $payments = $notDefaultAddress->getPaymentAccounts();

        $this->assertCount(1, $payments);

        $this->getEntityManager()->remove($payments[0]);
        $this->getEntityManager()->flush();

        $this->login($user->getEmail(), 'pass');

        $this->page->clickLink('common.account');
        $this->page->clickLink('settings.address.head.manage');

        $this->assertCount(2, $this->page->findAll('css', '#user-address-table>tbody>tr'));
        $this->assertNotNull(
            $deleteButton = $this->page->find(
                'css',
                'a.delete[href$=' . $notDefaultAddress->getId() . ']'
            )
        );

        $deleteButton->click();

        $this->page->clickLink('settings.address.action.delete.yes');

        $this->assertNotNull($error = $this->page->find('css', 'div.flash-notice'));
        $this->assertContains('settings.address.delete.success', $error->getText());

        $this->assertCount(1, $this->page->findAll('css', '#user-address-table>tbody>tr'));
    }
}
