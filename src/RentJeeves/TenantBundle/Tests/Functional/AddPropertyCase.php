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
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>.static'));
        $this->assertCount(5, $tr, 'List of property');
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
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>.static'));
        $this->assertCount(6, $tr, 'List of property');
        $this->logout();
    }

    /**
     * @test
     * @depends addWithLandlord
     */
    public function checkDuplicateContractAddWithLandlord()
    {
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>.static'));
        $this->assertCount(6, $tr, 'List of property');
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
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>.static'));
        $this->assertCount(6, $tr, 'List of property');
        $this->assertNotNull($errorMessage = $this->page->find('css', '#current-payments .attention-box.pie-el li'));
        $this->assertEquals('error.contract.duplicate', $errorMessage->getHtml());
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
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>.static'));
        $this->assertCount(5, $tr, 'List of property');
        $this->assertNotNull($addProperty = $this->page->find('css', '.addPropertyContainer a'));
        $addProperty->click();
        $this->session->wait($this->timeout, "window.location.pathname == '/rj_test.php/property/add'");
        $this->fillGoogleAddress('710 Broadway, Manhattan, New York City, NY 10003, United States');
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
        $this->fillGoogleAddress('770 Broadway, Manhattan, New York City, NY 10003, United States');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        $this->session->wait($this->timeout, "window.location.pathname.match('\/property\/add\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('.search-result-text li').length > 0");
        $this->assertNotNull($searchResult = $this->page->findAll('css', '.search-result-text li'));

        $this->session->evaluateScript(
            "$('#property-search').val(' ');"
        );
        $this->fillGoogleAddress('960 Andante Rd, Santa Barbara, CA 93105, United States');
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $propertySearch->click();
        $this->session->wait($this->timeout+10000, "window.location.pathname.match('\/property\/add\/[0-9]') != null");
        $this->session->wait($this->timeout+15000, "typeof jQuery !== undefined");
        $this->session->wait($this->timeout, "$('#formSearch').length > 0");

        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->fillForm(
            $form,
            array(
                'property-search' => '710 Broadway, Manhattan, New York City, NY 10003, United States',
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
                'rentjeeves_publicbundle_invitetype_unitName'                  => 'e3',
                'rentjeeves_publicbundle_invitetype_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetype_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetype_email'                     => 'newlandlord@etest.com',
            )
        );
        $this->assertNotNull($register = $this->page->find('css', '#register'));
        $this->page->pressButton('add.property');
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>.static'));
        $this->assertCount(6, $tr, 'List of property');

        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#payRentLinkLandlord')->getNode(0)->getAttribute('href');
        $this->clearEmail();

        $this->setDefaultSession('selenium2');
        $this->session->visit($url);
        $this->session->wait($this->timeout, '$("#invitelandlordtype").length > 0');
        $this->assertNotNull($form = $this->page->find('css', '#invitelandlordtype'));
        $form->pressButton('continue');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(2, $errorList, 'Wrong number of error');
        $this->fillForm(
            $form,
            array(
                'invitelandlordtype_landlord_password_Password'          => 'pass',
                'invitelandlordtype_landlord_password_Verify_Password'   => 'pass',
                'invitelandlordtype_landlord_tos'                        => true,
            )
        );
        $form->pressButton('continue');
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($contract = $this->page->findAll('css', '.properties-table tbody tr'));
        $this->assertCount(1, $contract, 'Wrong number of contract');
        $this->logout();
        //Check notify tenant about landlord come
        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }

    /**
     * @test
     */
    public function inviteLandlordAlreadyExist()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($tr = $this->page->findAll('css', '#current-payments .properties-table>tbody>tr'));
        $this->assertCount(5, $tr, 'List of property');
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
                'rentjeeves_publicbundle_invitetype_unitName'                      => 'e3',
                'rentjeeves_publicbundle_invitetype_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetype_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetype_email'                     => 'landlord2@example.com',
            )
        );
        $this->page->pressButton('add.property');
        $this->session->wait($this->timeout, "$('.properties-table').length > 0");
        $this->assertNotNull($tr = $this->page->findAll('css', '#current-payments .properties-table>tbody>tr'));
        $this->assertCount(6, $tr, 'List of property');
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

        $this->assertNotNull($searchField = $this->page->find('css', '#searchPaymentsStatus_link'));
        $searchField->click();
        $this->assertNotNull($current = $this->page->find('css', '#searchPaymentsStatus_li_1'));
        $current->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit-payments-status'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($allh2 = $this->page->find('css', '.title-box>h2'));
        $this->assertEquals('All (1)', $allh2->getText(), 'Wrong count of tenants');
        $this->assertNotNull($emailBox = $this->page->findAll('css', '.email-box'));
        $this->assertEquals('2', count($emailBox), 'Wrong count of email-box');
        $this->assertEquals('Timothy Applegate', $emailBox[0]->getText(), 'Wrong tenant');
        $this->assertEquals('tenant11@example.com', $emailBox[1]->getText(), 'Wrong tenant');
        $this->logout();
    }
}
