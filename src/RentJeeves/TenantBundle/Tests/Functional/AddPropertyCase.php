<?php
namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class AddPropertyCase extends BaseTestCase
{
    protected $timeout = 30000;

    protected function fillGoogleAddress($fillAddress)
    {
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
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
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->assertNotNull($addProperty = $this->page->find('css', '.addPropertyContainer a'));
        $addProperty->click();
        $this->session->wait($this->timeout, "window.location.pathname == '/rj_test.php/property/add'");
        $this->fillGoogleAddress('770 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $this->page->pressButton('find.your.rental');
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $register->click();
        $this->acceptAlert();
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $register->click();
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(5, $tr, 'List of property');
        $this->logout();
    }

    /**
     * @test
     */
    public function invite()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->assertNotNull($addProperty = $this->page->find('css', '.addPropertyContainer a'));
        $addProperty->click();
        $this->session->wait($this->timeout, "window.location.pathname == '/rj_test.php/property/add'");
        $this->fillGoogleAddress('710 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $this->page->pressButton('find.your.rental');
        $this->session->wait($this->timeout, "window.location.pathname != '/rj_test.php/property/add'");
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
        $this->assertNotNull($inviteLandlord = $this->page->find('css', '.inviteLandlord'));
        $inviteLandlord->click();
        $this->session->wait($this->timeout, "$('#register').length > 0");
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $this->page->pressButton('add.property');
        $this->assertNotNull($this->page->find('css', '.error_list'));
        $this->session->evaluateScript(
            "$('#property-search').val(' ');"
        );
        $this->fillGoogleAddress('770 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        $this->session->wait($this->timeout, "window.location.pathname.match('\/property\/add\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
        
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
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(4, $tr, 'List of property');
    }

    /**
     * @test
     * @depends invite
     */
    public function checkInvite()
    {
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->assertNotNull($link = $this->page->find('css', '#payRentLinkLandlord'));
        $url = $link->getAttribute('href');
        $this->setDefaultSession('selenium2');
        $this->session->visit($url);
        $this->session->wait($this->timeout, '$("#landlordInviteRegister").length > 0');
        $this->assertNotNull($form = $this->page->find('css', '#landlordInviteRegister'));
        $form->pressButton('continue');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(2, $errorList, 'Wrong number of pending');
        $this->fillForm(
            $form,
            array(
                'landlordType_password_Password'          => 'pass',
                'landlordType_password_Verify_Password'   => 'pass',
                'landlordType_tos'                        => true,
            )
        );
        $form->pressButton('continue');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(1, $contractPendings, 'Wrong number of pending');
    }

    /**
     * @test
     */
    public function inviteLandlordAlreadyExist()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(3, $tr, 'List of property');
        $this->assertNotNull($addProperty = $this->page->find('css', '.addPropertyContainer a'));
        $addProperty->click();
        $this->session->wait($this->timeout, "window.location.pathname == '/rj_test.php/property/add'");
        $this->fillGoogleAddress('710 Broadway, Manhattan, New York, NY 10003');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $this->page->pressButton('find.your.rental');
        $this->session->wait($this->timeout, "window.location.pathname != '/rj_test.php/property/add'");
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));
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
                'rentjeeves_publicbundle_invitetype_email'                     => 'landlord@yandex.ru',
            )
        );
        $this->page->pressButton('add.property');
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(4, $tr, 'List of property');
    }

    /**
     * @test
     * @depends inviteLandlordAlreadyExist
     */
    public function checkEmailInviteLandlordAlreadyExist()
    {
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->assertNotNull($link = $this->page->find('css', '#payRentLinkLandlord'));
        $url = $link->getAttribute('href');
        $this->setDefaultSession('selenium2');
        $this->session->visit($url);
        $this->session->wait($this->timeout, '$(".haveAccount a").length > 0');
        $this->assertNotNull($link = $this->page->find('css', '.haveAccount a'));
        $link->click();
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->login('landlord2@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        
        $this->assertNotNull($contract = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(1, $contract, 'Wrong number of pending');
        $this->logout();
    }
}
