<?php
namespace CreditJeeves\UserBundle\Tests\Traits;

trait SettingsCaseTrait
{
    /**
     * @test
     */
    public function userAddressesListSettings()
    {
        //$this->setDefaultSession('selenium2');
        $this->setDefaultSession('zombie');

        $this->load(true);
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.address.head.manage');

        /** @var DocumentElement[] $rows */
        $this->assertGreaterThanOrEqual(
            2,
            $rows = $this->page->findAll('css', '.addresses-table tbody tr'),
            'This user shoul hawe two or more fixtures.'
        );

        // Check that the default address isset and on the first position and does not have a delete link
        $this->assertNotNull(
            $rows[0]->find('css', '.default-address'),
            'The first row should have a "(default)" marker.'
        );
        $this->assertNull($rows[0]->find('css', 'a.type-ico.delete'), 'The first row should not have a "delete" link.');

        $this->logout();
    }

    /**
     * @test
     */
    public function userAddNewAddressSettings()
    {
        //$this->setDefaultSession('selenium2');
        $this->setDefaultSession('zombie');

        $this->load(true);
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.address.head.manage');

        /** @var DocumentElement[] $rows */
        $this->page->clickLink('settings.address.action.add');
        $this->assertNotNull(
            $form = $this->page->find('css', '#creditjeeves_userbundle_useraddresstype'),
            'Cant find form'
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
            'Can not find the added address on the frist position.'
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
        //$this->setDefaultSession('selenium2');
        $this->setDefaultSession('zombie');

        $this->load(true);
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.address.head.manage');

        /** @var DocumentElement[] $rows */
        $this->assertGreaterThanOrEqual(
            2,
            $rows = $this->page->findAll('css', '.addresses-table tbody tr'),
            'This user shoul hawe two or more fixtures.'
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
            'Can not find the edited address on the frist position.'
        );
        //var_dump($rows[0]->getHtml());die;
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
    public function userDeleteAddressSettings()
    {
        //$this->setDefaultSession('selenium2');
        $this->setDefaultSession('zombie');

        $this->load(true);
        $this->login($this->userEmail, 'pass');
        $this->page->clickLink($this->accountLink);
        $this->page->clickLink('settings.address.head.manage');

        /** @var DocumentElement[] $rows */
        $this->assertGreaterThanOrEqual(
            2,
            $rows = $this->page->findAll('css', '.addresses-table tbody tr'),
            'This user shoul hawe two or more fixtures.'
        );
        $this->assertNotNull($link = $rows[1]->find('css', 'a.type-ico.delete'), 'Can not find link to delete page.');
        $link->click();

        $this->assertEquals(
            count($rows) - 1,
            count($this->page->findAll('css', '.addresses-table tbody tr')),
            'Wrong number of rows.'
        );

        $this->logout();
    }
}
