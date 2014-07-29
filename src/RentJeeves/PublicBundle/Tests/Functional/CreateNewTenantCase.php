<?php

namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class CreateNewTenantCase extends BaseTestCase
{
    /**
     * @test
     */
    public function showCreateUserLink()
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
     * @depends showCreateUserLink
     */
    public function pageNewUserwhithoutProperty()
    {
        /**
         * check form that user cannot be created without propertyId
         */
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
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();

        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(1, $errorList, 'Wrong number of errors');
        $this->assertEquals('error.property.empty', $errorList[0]->getText());
        /**
         * check form redirect to
         */
        $fillAddress = '960 Andante Rd, Santa Barbara, CA 93105';

        $this->fillGoogleAddress($fillAddress);

        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
    }
}