<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class IframeCase extends BaseTestCase
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
        $this->session->wait($this->timeout, "$('div.pac-container').children().length > 0");
        $this->session->wait($this->timeout, "$('div.pac-container').is(':visible')");
        $this->assertNotNull($item = $this->page->find('css', 'div.pac-container div'));
        $item->click();
    }

    /**
     * @test
     */
    public function iframeNotFound()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'iframe');
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($submit = $form->findButton('iframe.find'));
        $submit->click();
        $this->assertNotNull($errorSearchIframe = $this->page->find('css', '.errorsGoogleSearch'));
        $this->assertEquals(
            'error.property.empty',
            $errorSearchIframe->getHtml()
        );
        $fillAddress = '45 Rockefeller Plaza, New York City, NY 10111';
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/invite\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_invitetenanttype').length > 0");
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unit'));
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(6, $errorList, 'Wrong number of errors');
        //Check search on the not found
        $fillAddress = 'Manhattan, New York City, NY 10118';
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#search-submit'));
        $this->session->executeScript(
            "$('#property-search').val('');"
        );
        $propertySearch->click();
        $this->assertNotNull($errors = $this->page->find('css', '.errorsGoogleSearch'));
        $this->assertEquals(
            'error.property.empty',
            $errors->getHtml()
        );
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $propertySearch->click();
        $this->session->wait($this->timeout, "$('.loadingSpinner').is(':visible')");
        $this->session->wait($this->timeout, "!$('.loadingSpinner').is(':visible')");
        $this->assertNotNull($errors = $this->page->find('css', '.errorsGoogleSearch'));
        $this->assertEquals(
            'property.number.not.exist',
            $errors->getHtml()
        );
        $fillAddress = '350 5th Avenue, Manhattan, New York City, NY 10118';
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $url = $this->session->getCurrentUrl();
        $propertySearch->click();
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('.loadingSpinner').is(':visible')");
        $this->session->wait($this->timeout, "!$('.loadingSpinner').is(':visible')");
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "document.URL != '{$url}'");
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#property-search').val() == '{$fillAddress}'");
        //end check search on the not found
        $this->page->clickLink('Pricing');
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#pricing-popup button.button-close'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $buttons[0]->click();
        $this->session->wait($this->timeout, "!$('#pricing-popup').is(':visible')");

        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_invitetenanttype_invite_unit'                      => 'e3',
                'rentjeeves_publicbundle_invitetenanttype_invite_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetenanttype_invite_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetenanttype_invite_email'                     => 'landlord@ya.ru',
                'rentjeeves_publicbundle_invitetenanttype_tenant_first_name'                => "Alex",
                'rentjeeves_publicbundle_invitetenanttype_tenant_last_name'                 => "Sharamko",
                'rentjeeves_publicbundle_invitetenanttype_tenant_email'                     => "newtenant@test.com",
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Password'         => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_tos'                       => true,
            )
        );
        
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(3, $fields, 'wrong number of text h4');
    }

    /**
     * @test
     * @depends iframeNotFound
     */
    public function checkEmailIframeNotFound()
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
        $this->login('newtenant@test.com', 'pass');
        //$this->assertNotNull($this->page->find('css', '.titleAlert'));
        $this->logout();
    }

    /**
     * @test
     * @depends checkEmailIframeNotFound
     */
    public function checkInviteIframeNotFound()
    {
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(2, $email, 'Wrong number of emails');
        $email = end($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->assertNotNull($link = $this->page->find('css', '#payRentLinkLandlord'));
        $url = $link->getAttribute('href');
        $this->setDefaultSession('selenium2');
        $this->session->visit($url);
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#invitelandlordtype').is(':visible')");
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
        
        $this->assertNotNull($contract = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(1, $contract, 'Wrong number of pending');
    }

    /**
     * @test
     */
    public function iframeFound()
    {
        $this->setDefaultSession('selenium2');
        $this->clearEmail();
        $this->logout();
        $this->session->visit($this->getUrl() . 'iframe');
        $fillAddress = '770 Broadway, Manhattan, New York City, NY 10003';
        $this->session->visit($this->getUrl() . 'iframe');
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#register').length > 0");
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $submit->click();
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(5, $errorList, 'Wrong number of errors');
        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name'                => "Alex",
                'rentjeeves_publicbundle_tenanttype_last_name'                 => "Sharamko",
                'rentjeeves_publicbundle_tenanttype_email'                     => "newtenant13@yandex.ru",
                'rentjeeves_publicbundle_tenanttype_password_Password'         => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos'                       => true,
            )
        );
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(2, $fields, 'wrong number of text h4');
    }

    /**
     * @test
     * @depends iframeFound
     */
    public function iframeFoundCheckEmail()
    {
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(1, $email, 'Wrong number of emails');
        $email = array_pop($email);
        $email->click();
        $this->page->clickLink('text/html');
        $this->assertEquals(
            1,
            preg_match(
                "/Please visit.*href=\"(.*)\".*to confirm your registration/is",
                $this->page->getContent(),
                $matches
            )
        );
        $this->assertNotEmpty($matches[1]);
        $this->session->visit($matches[1]);
        $this->assertNotNull($loginButton = $this->page->find('css', '#loginButton'));
        $loginButton->click();
        $this->login('newtenant13@yandex.ru', 'pass');
        //$this->assertNotNull($this->page->find('css', '.titleAlert'));
        $this->assertNotNull($contracts = $this->page->findAll('css', '.contracts'));
    }

    /**
     * @test
     */
    public function checkNotFoundNew()
    {
        $this->setDefaultSession('selenium2');
        $this->session->visit($this->getUrl() . 'iframe');
        $fillAddress = '770 Broadway, Manhattan, New York City, NY 10003';
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#formNewUser').length > 0");
        $fillAddress = '710 Broadway, Manhattan, New York City, NY 10003 ';
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $this->session->executeScript(
            "$('#property-search').val(' ');"
        );
        $propertySearch->click();
        $propertySearch->setValue($fillAddress);
        $propertySearch->click();
        $this->session->wait($this->timeout, "$('div.pac-container').children().length > 0");
        $this->session->wait($this->timeout, "$('div.pac-container').is(':visible')");
        $this->assertNotNull($item = $this->page->find('css', 'div.pac-container div'));
        $item->click();
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit>span'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "$('.inviteLandlord').is(':visible')");
        $this->assertNotNull($inviteLandlord = $this->page->find('css', '.inviteLandlord'));
        $inviteLandlord->click();
        
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait(
            $this->timeout,
            "$('#rentjeeves_publicbundle_invitetenanttype_invite_first_name').length > 0"
        );
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unit'));
    }
}
