<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class AccountCase extends BaseTestCase
{
    /**
     * @test
     */
    public function accountInfo()
    {
        $this->markTestIncomplete('FINISH');
        $this->setDefaultSession('goutte'); //@TODO need change to symfony, becouse it will be faster
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('common.account');
        $this->assertNotNull($form = $this->page->find('css', '#landlordAccountInformation'));
        $this->fillForm(
            $form,
            array(
                'account_info_first_name'                         => "",
                'account_info_last_name'                          => "",
                'account_info_email'                              => "",
                'account_info_phone'                              => "",
                'account_info_address_street'                     => '',
                'account_info_address_city'                       => '',
                'account_info_address_zip'                        => '',
                'account_info_address_unit'                       => '',
            )
        );
        $form->pressButton('savechanges');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(5, $errorList, 'Error list');
        $this->assertNotNull($form = $this->page->find('css', '#landlordAccountInformation'));
        $this->fillForm(
            $form,
            array(
                'account_info_first_name'                         => "Alex",
                'account_info_last_name'                          => "Sharamko",
                'account_info_email'                              => "landlord13@yandex.ru",
                'account_info_phone'                              => "123.333.1234",
                'account_info_address_street'                     => 'My Street',
                'account_info_address_city'                       => 'Test',
                'account_info_address_zip'                        => '1231',
                'account_info_address_area'                       => 'AL',
                'account_info_address_unit'                       => '1231'
            )
        );
        $form->pressButton('savechanges');
        $this->assertNotNull($name = $this->page->find('css', '#account_info_first_name'));
        $this->assertNotNull($city = $this->page->find('css', '#account_info_address_city'));
        $this->assertEquals('Alex', $name->getValue(), 'Wrong text in field');
        $this->assertEquals('Test', $city->getValue(), 'Wrong text in field');
        $this->logout();
    }
}
