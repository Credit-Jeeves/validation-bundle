<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class AddPropertyCase extends BaseTestCase
{
    protected $timeout = 25000;

    protected function fillGoogleAddress($fillAddress)
    {
        $this->session->wait($this->timeout, "$('#formSearch').length > 0");
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $propertySearch->click();
        $this->session->wait($this->timeout, "$('.pac-item').length > 0");
        $this->session->wait($this->timeout, "$('.pac-item').parent().is(':visible')");
        $this->assertNotNull($item = $this->page->find('css', '.pac-item'));
        $item->click();
    }

    /**
     * @test
     */
    public function addWithLandlord()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($tr = $this->page->findAll('css', '.listOfPayments>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->assertNotNull($addProperty = $this->page->find('css', '.addPropertyContainer a'));
        $addProperty->click();
        $this->fillGoogleAddress('770 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
        $this->assertCount(4, $searchResult, 'Search result');
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $register->click();
        $this->acceptAlert();
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $register->click();
        $this->session->wait($this->timeout, "$('.listOfPayments').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.listOfPayments>tbody>tr'));
        $this->assertCount(5, $tr, 'List of property');
        $this->logout();
    }

    /**
     * @test
     */
    public function invite()
    {
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($tr = $this->page->findAll('css', '.listOfPayments>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->assertNotNull($addProperty = $this->page->find('css', '.addPropertyContainer a'));
        $addProperty->click();
        $this->fillGoogleAddress('710 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $this->page->pressButton('find.your.rental');
        $propertySearch->click();
        
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
        $this->assertCount(5, $searchResult, 'Search result');
        $this->assertNotNull($inviteLandlord = $this->page->find('css', '.inviteLandlord'));
        $inviteLandlord->click();
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $this->page->pressButton('add.property');
        $this->assertNotNull($this->page->find('css', '.error_list'));
        $this->session->evaluateScript(
            "$('#property-search').val(' ');"
        );
        $this->fillGoogleAddress('770 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
        $this->assertCount(4, $searchResult, 'Search result');
        
        $this->session->evaluateScript(
            "$('#property-search').val(' ');"
        );
        $this->fillGoogleAddress('710 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        
        $this->session->wait($this->timeout, "$('.inviteLandlord').length > 0");
        $this->assertNotNull($inviteLandlord = $this->page->find('css', '.inviteLandlord'));
        $inviteLandlord->click();
        $this->session->wait($this->timeout, "$('#inviteForm').length > 0");
        $this->assertNotNull($inviteForm = $this->page->find('css', '#inviteForm'));
        $this->fillForm(
            $inviteForm,
            array(
                'rentjeeves_publicbundle_invitetype_unit'                      => 'e3',
                'rentjeeves_publicbundle_invitetype_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetype_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetype_email'                     => 'newtenant@yandex.ru',
            )
        );
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $this->page->pressButton('add.property');
        $this->session->wait($this->timeout, "$('.listOfPayments').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.listOfPayments>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
    }
}
