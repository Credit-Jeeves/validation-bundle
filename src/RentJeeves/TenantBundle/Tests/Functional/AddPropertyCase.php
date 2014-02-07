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
        $this->assertCount(4, $tr, 'List of property');
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
        $this->assertNotNull($errorMessage = $this->page->find('css', '#errorMessage'));
        $this->assertEquals('select.rental', $errorMessage->getText());
        $this->session->visit($this->session->getCurrentUrl());
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
        $this->assertCount(4, $tr, 'List of property');
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
        $this->fillGoogleAddress('560 Broadway, Manhattan, New York, NY 10012');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        $this->session->wait($this->timeout+10000, "window.location.pathname.match('\/property\/add\/[0-9]') != null");
        $this->session->wait($this->timeout+15000, "typeof jQuery !== undefined");
        $this->session->wait($this->timeout, "$('#formSearch').length > 0");

        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->fillForm(
            $form,
            array(
                'property-search' => '710 Broadway, Manhattan, New York, NY 10003',
            )
        );
        $this->page->pressButton('find.your.rental');
        $this->session->wait($this->timeout, "$('.inviteLandlord').length > 0");
        $this->assertNotNull($inviteLandlord = $this->page->find('css', '.inviteLandlord'));
        $inviteLandlord->click();
        $this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_invitetype').length > 0");
        $this->assertNotNull($inviteForm = $this->page->find('css', '#rentjeeves_publicbundle_invitetype'));
        $this->fillForm(
            $inviteForm,
            array(
                'rentjeeves_publicbundle_invitetype_unit'                      => 'e3',
                'rentjeeves_publicbundle_invitetype_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetype_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetype_email'                     => 'newlandlord@test.com',
            )
        );
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $this->page->pressButton('add.property');
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(5, $tr, 'List of property');
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
        $this->clearEmail();
        $this->setDefaultSession('selenium2');
        $this->session->visit($url);
        $this->session->wait($this->timeout, '$("#invitelandlordtype").length > 0');
        $this->assertNotNull($form = $this->page->find('css', '#invitelandlordtype'));
        $form->pressButton('continue');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(6, $errorList, 'Wrong number of pending');
        $this->fillForm(
            $form,
            array(
                'invitelandlordtype_landlord_password_Password'          => 'pass',
                'invitelandlordtype_landlord_password_Verify_Password'   => 'pass',
                'invitelandlordtype_deposit_nickname'                    => 'nickname',
                'invitelandlordtype_deposit_AccountNumber'               => '12345678',
                'invitelandlordtype_deposit_RoutingNumber'               => '12345678',
                'invitelandlordtype_deposit_ACHDepositType_1'            => true,
                'invitelandlordtype_landlord_tos'                        => true,
            )
        );
        $form->pressButton('continue');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($contract = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(1, $contract, 'Wrong number of contract');
        //Check notify tenant about landlord come
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
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
        $this->assertCount(4, $tr, 'List of property');
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

        $this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_invitetype').length > 0");
        $this->assertNotNull($inviteForm = $this->page->find('css', '#rentjeeves_publicbundle_invitetype'));
        $this->fillForm(
            $inviteForm,
            array(
                'rentjeeves_publicbundle_invitetype_unit'                      => 'e3',
                'rentjeeves_publicbundle_invitetype_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetype_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetype_email'                     => 'landlord2@example.com',
            )
        );
        $this->page->pressButton('add.property');
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(5, $tr, 'List of property');
    }

    /**
     * @test
     * @depends inviteLandlordAlreadyExist
     */
    public function checkEmailInviteLandlordAlreadyExist()
    {
        $this->login('landlord2@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($contract = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(3, $contract, 'Wrong number of contracts');
        $this->logout();
    }
}
