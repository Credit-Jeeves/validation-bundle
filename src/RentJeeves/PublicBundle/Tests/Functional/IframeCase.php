<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

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
        $this->session->wait($this->timeout, "$('.pac-item').length > 0");
        $this->session->wait($this->timeout, "$('.pac-item').parent().is(':visible')");
        $this->assertNotNull($item = $this->page->find('css', '.pac-item'));
        $item->click();
        $propertySearch->click();
        $this->assertNotNull($submit = $form->findButton('iframe.find'));
        $submit->click();
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
        $this->acceptAlert();
        $fillAddress = '30 Rockefeller Plaza, New York City, NY 10112';
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/invite\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#inviteForm').length > 0");
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unit'));
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(6, $errorList, 'Wrong number of errors');
        //Check search on the not found
        $fillAddress = 'Manhattan, New York City, NY 10118';
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $this->session->executeScript(
            "$('#property-search').val(' ');"
        );
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
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $url = $this->session->getCurrentUrl();
        $searchSubmit->click();
        $this->session->wait($this->timeout, "document.URL != '{$url}'");
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#property-search').val() == '{$fillAddress}'");
        //end check search on the not found

        $this->assertNotNull($form = $this->page->find('css', '#inviteForm'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_invitetenanttype_invite_unit'                      => 'e3',
                'rentjeeves_publicbundle_invitetenanttype_invite_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetenanttype_invite_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetenanttype_invite_email'                     => 'newtenant@yandex.ru',
                'rentjeeves_publicbundle_invitetenanttype_tenant_first_name'                => "Alex",
                'rentjeeves_publicbundle_invitetenanttype_tenant_last_name'                 => "Sharamko",
                'rentjeeves_publicbundle_invitetenanttype_tenant_email'                     => "newtenant12@yandex.ru",
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Password'         => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_tos'                       => true,
            )
        );
        
        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(3, $fields, 'wrong number of text h4');
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
        $this->login('newtenant12@yandex.ru', 'pass');
        $this->assertNotNull($this->page->find('css', '.titleAlert'));
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(2, $email, 'Wrong number of emails');
    }

    /**
     * @test
     */
    public function iframeFound()
    {
        $this->setDefaultSession('selenium2');
        $this->clearEmail();
        $this->session->visit($this->getUrl() . 'iframe');
        $fillAddress = '770 Broadway, Manhattan, New York City, NY 10003';
        $this->session->visit($this->getUrl() . 'iframe');
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#register').length > 0");
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
        $this->acceptAlert();
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
        $this->login('newtenant13@yandex.ru', 'pass');
        $this->assertNotNull($this->page->find('css', '.titleAlert'));
        $this->assertNotNull($contracts = $this->page->findAll('css', '.contracts'));
        $this->assertCount(2, $contracts, 'wrong number of contracts');
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
        $this->session->wait($this->timeout, "$('.pac-item').length > 0");
        $this->session->wait($this->timeout, "$('.pac-item').parent().is(':visible')");
        $this->assertNotNull($item = $this->page->find('css', '.pac-item'));
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
