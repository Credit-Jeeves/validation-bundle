<?php
/**
 * Created by PhpStorm.
 * User: yurez
 * Date: 25.07.14
 * Time: 13:41
 */

namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class CreateNewTenantCase extends BaseTestCase
{
    private function start()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'login');
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $this->assertNotNull($link = $this->page->find('css', '#create-user'));

        $link->click();

        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new') != null");


    }

    private function fillGoogleAddress($fillAddress)
    {
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($btnSearch = $this->page->findButton('search-submit'));
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $btnSearch->click();
    }

    /**
     * @test
     */
    public function createTenantLandlordPresent()
    {
        $this->start();
        $fillAddress = '960 Andante Rd, Santa Barbara, CA 93105';

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
                'rentjeeves_publicbundle_tenanttype_first_name' => "Alex",
                'rentjeeves_publicbundle_tenanttype_last_name' => "Sharamko",
                'rentjeeves_publicbundle_tenanttype_email' => 'newtenant55@gmail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            )
        );
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
    }



    /**
     * @test
     */
    public function createTenantLandlordNotPresent()
    {
        $this->start();

        $fillAddress = '350 5th Avenue, Manhattan, New York City, NY 10118, United States';

        $this->fillGoogleAddress($fillAddress);


        $this->assertNotNull($invite= $this->page->find('css', 'a.inviteLandlord'));

        $invite->click();

        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/invite\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_invitetenanttype').length > 0");
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unit'));
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(7, $errorList, 'Wrong number of errors');
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
        $fillAddress = '350 5th Avenue, Manhattan, New York City, NY 10118, United States';
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $url = $this->session->getCurrentUrl();
        $propertySearch->click();
        $this->session->wait($this->timeout + 10000, "typeof $ !== undefined");
        $this->session->wait($this->timeout, "$('.loadingSpinner').is(':visible')");
        $this->session->wait($this->timeout, "!$('.loadingSpinner').is(':visible')");
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $searchSubmit->click();
        $this->session->wait($this->timeout, "document.URL != '{$url}'");
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
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
                'rentjeeves_publicbundle_invitetenanttype_invite_is_single' => false,
                'rentjeeves_publicbundle_invitetenanttype_invite_unit' => 'e3',
                'rentjeeves_publicbundle_invitetenanttype_invite_first_name' => 'Alex',
                'rentjeeves_publicbundle_invitetenanttype_invite_last_name' => 'Sharamko',
                'rentjeeves_publicbundle_invitetenanttype_invite_email' => 'landlord56@gmail.com',
                'rentjeeves_publicbundle_invitetenanttype_tenant_first_name' => "Alex",
                'rentjeeves_publicbundle_invitetenanttype_tenant_last_name' => "Sharamko",
                'rentjeeves_publicbundle_invitetenanttype_tenant_email' => "newtenant56@test.com",
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Password' => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Verify_Password' => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_tos' => true,
            )
        );

        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(3, $fields, 'wrong number of text h4');

    }
} 