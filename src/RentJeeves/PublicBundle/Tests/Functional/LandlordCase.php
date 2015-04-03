<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

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
        $this->assertCount(8, $errorList, 'Error list');
        $fillAddress = '13 Greenwich St, Manhattan, New York, NY 10013, United States';
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
            )
        );
        $this->assertNotNull($addUnit = $this->page->find('css', '#addUnit>span'));
        $addUnit->click();
        $this->session->wait($this->timeout, "$('.unit-name:visible').length == 3");
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-name'));
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');

        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();

        $this->assertEquals($this->getUrl() . 'landlord/tenants', $this->session->getCurrentUrl());
    }

    /**
     * @test
     */
    public function landlordRegistersWithSingleProperty()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'landlord/register/');
        $fillAddress = '13 Greenwich St, Manhattan, New York, NY 10013, United States';
        $this->fillGoogleAddress($fillAddress);

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
            )
        );
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->session->wait($this->timeout, "$('.error_list').length > 0");
        $errorList = $this->page->findAll('css', '.error_list');
        $this->assertCount(1, $errorList);
        $this->assertEquals('units.error.add_or_mark_single', $errorList[0]->getText());

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
            )
        );
        $this->assertNotNull($addUnit = $this->page->find('css', '#addUnit>span'));
        $addUnit->click();
        $this->session->wait($this->timeout, "$('.unit-name:visible').length == 3");
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-name'));
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');

        $submit->click();

        $this->assertEquals($this->getUrl() . 'landlord/tenants', $this->session->getCurrentUrl());
    }

    /**
     * @test
     * @depends landlordRegistersWithSingleProperty
     */
    public function landlordLogin()
    {
        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#email-body')->filter('a')->getNode(0)->getAttribute('href');
        $this->session->visit($url);
        $this->assertNotNull($loginButton = $this->page->find('css', '#loginButton'));
        $loginButton->click();
        $this->setDefaultSession('selenium2');
        $this->login('newlandlord12@yandex.ru', 'pass');
        $this->page->clickLink('tabs.properties');

        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
    }

    /**
     * @test
     */
    public function landlordResendInvite()
    {
        $this->setDefaultSession('goutte');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'landlord/register/');
        $this->assertNotNull($form = $this->page->find('css', '#LandlordAddressType'));

        $this->fillForm(
            $form,
            array(
                'LandlordAddressType_landlord_email'    => "landlord1@example.com",
            )
        );
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->assertNotNull($userExistMessage = $this->page->find('css', '#userExistMessage'));
        $this->assertEquals('user.email.already.exist', $userExistMessage->getText());

        $this->assertNotNull($form = $this->page->find('css', '#LandlordAddressType'));
        $this->fillForm(
            $form,
            array(
                'LandlordAddressType_landlord_email'    => "landlord2@example.com",
            )
        );
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->assertNotNull($userExistMessage = $this->page->find('css', '#userExistMessage'));
        $this->assertEquals('already.invited.error', $userExistMessage->getText());

        $user = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('DataBundle:User')
            ->findOneBy(
                array(
                    'email' => 'landlord2@example.com'
                )
            );
        $link = $this->getUrl() . 'landlord/invite/resend/'.$user->getId();
        $this->session->visit($link);
        $this->assertNotNull($title = $this->page->find('css', '.title'));
        $this->assertEquals('verify.email.invite.title', $title->getText());
        $this->session->visit($link);
        $this->assertNotNull($title = $this->page->find('css', '.title'));
        $this->assertEquals('error.oops', $title->getText());
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }
}
