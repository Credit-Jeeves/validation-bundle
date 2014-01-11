<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class LandlordCase extends BaseTestCase
{
    protected function fillGoogleAddress($fillAddress)
    {
        $this->assertNotNull($form = $this->page->find('css', '#LandlordAddressType'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $propertySearch->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $searchSubmit->click();
    }

    /**
     * @test
     */
    public function landlordRegisterTest()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'landlord/register/');
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->session->wait($this->timeout, "$('.error_list').length > 0");
        $errorList = $this->page->findAll('css', '.error_list');
        $this->assertCount(12, $errorList, 'Error list');
        $fillAddress = 'Top of the Rock Observation Deck, Rockefeller Plaza, New York City, NY 10112';
        $this->fillGoogleAddress($fillAddress);
        $this->page->clickLink('Pricing');
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#pricing-popup button.button-close'));
        $this->assertCount(1, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!$('#pricing-popup').is(':visible')");
        
        $this->assertNotNull($form = $this->page->find('css', '#LandlordAddressType'));
        $this->fillForm(
            $form,
            array(
                'LandlordAddressType_address_unit'                       => 'e3',
                'LandlordAddressType_landlord_first_name'                => 'Alex',
                'LandlordAddressType_landlord_last_name'                 => 'Sharamko',
                'LandlordAddressType_landlord_email'                     => "newlandlord12@yandex.ru",
                'LandlordAddressType_landlord_password_Password'         => 'pass',
                'LandlordAddressType_landlord_password_Verify_Password'  => 'pass',
                'LandlordAddressType_landlord_tos'                       => true,
                'LandlordAddressType_address_street'                     => 'My Street',
                'LandlordAddressType_address_city'                       => 'Test',
                'LandlordAddressType_address_zip'                        =>'1231',
                'numberOfUnit'                                           => 3,
                'LandlordAddressType_deposit_nickname'                   => 'Nick',
                'LandlordAddressType_deposit_AccountNumber'              => '12345678',
                'LandlordAddressType_deposit_RoutingNumber'              => '12345678',
                'LandlordAddressType_deposit_ACHDepositType_0'           => true,
            )
        );
        $this->assertNotNull($addUnit = $this->page->find('css', '#addUnit>span'));
        $addUnit->click();
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-name'));
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');

        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $currentUrl = $this->session->getCurrentUrl();
        $submit->click();

        $this->session->wait($this->timeout, "$('#main-content-wrapper').is(':visible')");

        $this->assertEquals(
            'https://onlineboarding.heartlandpaymentsystems.com/Wizard/Wizard/CardProcessing',
            $this->session->getCurrentUrl()
        );
    }

    /**
     * @test
     */
    public function landlordLogin()
    {
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->assertNotNull($link = $this->page->find('css', '#email-body a'));
        $link->click();
        $this->assertNotNull($loginButton = $this->page->find('css', '#loginButton'));
        $loginButton->click();
        $this->setDefaultSession('selenium2');
        $this->login('newlandlord12@yandex.ru', 'pass');
        $this->page->clickLink('tabs.properties');

        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
    }
}
