<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use DateTime;

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
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-add'));
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $propertySearch->click();
    }

    public function provideGoogleAddress()
    {
        return array(
            array('50 Orange Street, Brooklyn, NY 11201', 'Brooklyn', 'Brooklyn', 40.699021, -73.993744),
            array('13 Greenwich St, Manhattan, New York, NY 10013', 'New York', 'Manhattan', 40.7218084, -74.0097316),
        );
    }

    /**
     * @test
     * @dataProvider provideGoogleAddress
     */
    public function parseGoogleAddress($address, $city, $district, $jb, $kb)
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'iframe');
        $this->session->wait($this->timeout, "typeof $ !== undefined");

        $this->fillGoogleAddress($address);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/invite\/[0-9]') != null");

        $repo = $this->getContainer()->get('doctrine')->getManager()->getRepository('RjDataBundle:Property');
        $this->assertNotNull($property = $repo->findOneByJbKbWithUnitAndAlphaNumericSort($jb, $kb));
        $this->assertEquals($city, $property->getCity());
        $this->assertEquals($district, $property->getDistrict());
    }

    /**
     * @test
     */
    public function iframeInviteLandlord()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'iframe');
        $this->session->wait($this->timeout, "typeof $ !== undefined");
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($submit = $form->findButton('iframe.find'));
        $fillAddress = '150 Amsterdam Avenue, Manhattan, New York, NY 10023';
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/invite\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_invitetenanttype').length > 0");
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unitName'));

        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_invitetenanttype_invite_is_single'                 => false,
                'rentjeeves_publicbundle_invitetenanttype_invite_unitName'                      => '',
                'rentjeeves_publicbundle_invitetenanttype_invite_first_name'                => 'Met',
                'rentjeeves_publicbundle_invitetenanttype_invite_last_name'                 => 'Torr',
                'rentjeeves_publicbundle_invitetenanttype_invite_email'                     => 'landlord@ya.ru',
                'rentjeeves_publicbundle_invitetenanttype_tenant_first_name'                => "Andrew",
                'rentjeeves_publicbundle_invitetenanttype_tenant_last_name'                 => "Ssiw",
                'rentjeeves_publicbundle_invitetenanttype_tenant_email'                     => "newtenant@test.com",
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Password'         => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_tos'                       => true,
            )
        );

        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(1, $errorList);
        $this->assertEquals('invite.error.specify_unit_or_mark_single', $errorList[0]->getText());

        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_invitetenanttype_invite_is_single'                 => true,
                'rentjeeves_publicbundle_invitetenanttype_invite_unitName'                      => '',
                'rentjeeves_publicbundle_invitetenanttype_invite_first_name'                => 'Met',
                'rentjeeves_publicbundle_invitetenanttype_invite_last_name'                 => 'Torr',
                'rentjeeves_publicbundle_invitetenanttype_invite_email'                     => 'landlord@ya.ru',
                'rentjeeves_publicbundle_invitetenanttype_tenant_first_name'                => "Andrew",
                'rentjeeves_publicbundle_invitetenanttype_tenant_last_name'                 => "Ssiw",
                'rentjeeves_publicbundle_invitetenanttype_tenant_email'                     => "newtenant@test.com",
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Password'         => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_invitetenanttype_tenant_tos'                       => true,
            )
        );
        $submit->click();
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");

        $fields = $this->page->findAll('css', '#inviteText>h4');
        $this->assertCount(3, $fields, 'wrong number of text h4');
    }

    /**
     * @test
     */
    public function iframeNotFound()
    {
        $this->setDefaultSession('selenium2');
        $this->session->getDriver()->resizeWindow(1600, 1200);
        $this->load(true);
        $this->session->visit($this->getUrl() . 'iframe');
        $this->session->wait($this->timeout, "typeof $ !== undefined");
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
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unitName'));
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
        $this->session->wait($this->timeout, "!$('#formSearch img.loadingSpinner').is(':visible')");
        $this->assertNotNull($errors = $this->page->find('css', '.errorsGoogleSearch'));
        $this->assertEquals(
            'property.number.not.exist',
            $errors->getHtml()
        );
        $fillAddress = '150 Amsterdam Avenue, Manhattan, New York, NY 10023';
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $propertySearch->click();
        $this->session->wait($this->timeout, "!$('#formSearch img.loadingSpinner').is(':visible')");
        $this->session->wait($this->timeout, "$('#property-search').val() == '{$fillAddress}'");
        //end check search on the not found
        $this->assertNotNull($pricing = $this->page->find('css', '#popup-pricing'));
        $pricing->click();
        $this->session->wait($this->timeout, "$('#pricing-popup').is(':visible')");
        $this->assertNotNull($buttons = $this->page->findAll('css', '#pricing-popup button.button-close'));
        $this->assertCount(2, $buttons, 'Wrong number of buttons');
        $this->session->wait($this->timeout, "$('#pricing-popup button.button-close').is(':visible')");
        $buttons[0]->click();

        $this->session->wait($this->timeout, "!$('#pricing-popup').is(':visible')");

        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_invitetenanttype_invite_is_single'                 => false,
                'rentjeeves_publicbundle_invitetenanttype_invite_unitName'                      => 'e3',
                'rentjeeves_publicbundle_invitetenanttype_invite_first_name'                => 'Alex',
                'rentjeeves_publicbundle_invitetenanttype_invite_last_name'                 => 'Sharamko',
                'rentjeeves_publicbundle_invitetenanttype_invite_email'                     => 'landlord@ya.ru',
                'rentjeeves_publicbundle_invitetenanttype_tenant_first_name'                => "Alex",
                'rentjeeves_publicbundle_invitetenanttype_tenant_last_name'                 => "Sharamko",
                'rentjeeves_publicbundle_invitetenanttype_tenant_email'                     => "newtenant@etest.com",
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
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#email-body')->filter('a')->getNode(0)->getAttribute('href');

        $this->session->visit($url);
        $this->assertNotNull($loginButton = $this->page->find('css', '#loginButton'));
        $loginButton->click();
        $this->login('newtenant@etest.com', 'pass');
        //$this->assertNotNull($this->page->find('css', '.titleAlert'));
        $this->logout();
    }

    /**
     * @test
     * @depends checkEmailIframeNotFound
     */
    public function checkInviteIframeNotFound()
    {
        $emails = $this->getEmails();
        $this->assertCount(2, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $crawler = $this->getCrawlerObject($email->getBody());
        $url = $crawler->filter('#payRentLinkLandlord')->getNode(0)->getAttribute('href');

        $this->setDefaultSession('selenium2');
        $this->session->visit($url);
        $this->session->wait($this->timeout, "typeof jQuery != 'undefined'");
        $this->session->wait($this->timeout, "$('#invitelandlordtype').is(':visible')");
        $this->assertNotNull($form = $this->page->find('css', '#invitelandlordtype'));
        $form->pressButton('continue');
        $this->assertNotNull($errorList = $this->page->findAll('css', '.error_list'));
        $this->assertCount(2, $errorList, 'Wrong number of pending');
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
        $this->session->wait($this->timeout, "$('table.properties-table').length");

        $this->assertNotNull($contract = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(1, $contract, 'Wrong number of pending');
    }

    private function getIframeFound($email)
    {
        $this->setDefaultSession('selenium2');
        $this->clearEmail();
        $this->logout();
        $this->session->visit($this->getUrl() . 'iframe');
        $fillAddress = '960 Andante Rd, Santa Barbara, CA 93105, United States';
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
                'rentjeeves_publicbundle_tenanttype_email'                     => $email,
                'rentjeeves_publicbundle_tenanttype_password_Password'         => 'pass',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'  => 'pass',
                'rentjeeves_publicbundle_tenanttype_tos'                       => true,
            )
        );

        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();
    }

    /**
     * @test
     */
    public function iframeFound()
    {
        $this->getIframeFound('newtenant13@yandex.ru');
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
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Wrong number of emails');
        $email = $this->getEmailReader()->getEmail(array_pop($emails))->getMessage('text/html');
        $this->assertEquals(
            1,
            preg_match(
                "/Please visit.*href=\"(.*)\".*to confirm your registration/is",
                $email->getBody(),
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
        $fillAddress = '960 Andante Rd, Santa Barbara, CA 93105, United States';
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
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unitName'));
    }

    /**
     * @test
     */
    public function publicIframe()
    {
        $this->setDefaultSession('selenium2');
        $this->clearEmail();
        $this->logout();
        $fillAddress = '960 Andante Rd, Santa Barbara, CA 93105, United States';
        $this->session->visit($this->getUrl() . 'public_iframe?af=CREDITCOM');
        $this->session->wait($this->timeout, "$('#property-add').length > 0");
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/new\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#register').length > 0");
        $this->assertNotNull($this->page->find('css', '.creditcom-logo'));
        $this->assertNotNull($cookie = $this->session->getCookie('affiliateSource'));
        $this->assertEquals('CREDITCOM', $cookie);
        $this->assertNotNull($submit = $this->page->find('css', '#register'));

        $this->assertNotNull($form = $this->page->find('css', '#formNewUser'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_tenanttype_first_name'                => "Gary",
                'rentjeeves_publicbundle_tenanttype_last_name'                 => "Steel",
                'rentjeeves_publicbundle_tenanttype_email'                     => "email10@com.com",
                'rentjeeves_publicbundle_tenanttype_password_Password'         => '111',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password'  => '111',
                'rentjeeves_publicbundle_tenanttype_tos'                       => true,
            )
        );
        $this->assertNotNull($thisIsMyRental = $this->page->find('css', '.thisIsMyRental'));
        $thisIsMyRental->click();
        $this->assertNotNull($submit = $this->page->find('css', '#register'));
        $submit->click();

        $this->assertNull($cookie = $this->session->getCookie('affiliateSource'));
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        /** @var User $user  */
        $this->assertNotNull(
            $user = $em->getRepository('DataBundle:User')->findOneBy(array('email' => 'email10@com.com'))
        );
        $this->assertNotNull($partnerCode = $user->getPartnerCode());
        $this->assertNull($partnerCode->getFirstPaymentDate());
        $this->assertFalse($partnerCode->getIsCharged());
        $this->assertEquals('CREDITCOM', $partnerCode->getPartner()->getRequestName());

        $order = new OrderSubmerchant();
        $operation = new Operation();
        $contract = new Contract();
        $contract->setPaidTo(new DateTime());
        /** @var Property $property */
        $property = $em->getRepository('RjDataBundle:Property')
            ->findOneByJbKbWithUnitAndAlphaNumericSort(40.7426129, -73.9828048);
        $unit = $property->getExistingSingleUnit();
        $contract->setProperty($property);
        $contract->setUnit($unit);
        $contract->setStatus(ContractStatus::PENDING);
        $contract->setGroup($property->getPropertyGroups()->first());
        $operation->setType(OperationType::RENT);
        $operation->setContract($contract);
        $operation->setPaidFor($contract->getPaidTo());
        $order->addOperation($operation);
        $order->setUser($user);
        $order->setPaymentType(OrderPaymentType::CASH);
        $order->setSum(100);

        $this->getContainer()->get('payment_processor.order_status_manager')->setNew($order);

        $this->getEntityManager()->refresh($partnerCode);

        $date = new DateTime();
        $this->assertEquals($date->format('Y-m-d'), $partnerCode->getFirstPaymentDate()->format('Y-m-d'));
        $this->assertFalse($partnerCode->getIsCharged());
    }

    protected function checkResendInvite()
    {
        $this->assertNotNull($userExistMessage = $this->page->find('css', '#userExistMessage'));
        $this->assertEquals('already.invited.error', $userExistMessage->getText());
        $user = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('DataBundle:User')
            ->findOneBy(
                array(
                    'email' => 'connie@rentrack.com'
                )
            );
        $this->session->visit($this->getUrl() . 'tenant/invite/resend/'.$user->getId());
        $this->assertNotNull($title = $this->page->find('css', '.title'));
        $this->assertEquals('verify.email.invite.title', $title->getText());
        $this->session->visit($this->getUrl() . 'tenant/invite/resend/'.$user->getId());
        $this->assertNotNull($title = $this->page->find('css', '.title'));
        $this->assertEquals('error.oops', $title->getText());

        $this->assertCount(1, $this->getEmails(), 'Wrong number of emails');
    }

    /**
     * @test
     */
    public function resendInviteIframeFound()
    {
        $this->load(true);
        $this->getIframeFound('connie@rentrack.com');
        $this->checkResendInvite();
        // Close browser and reset session by thist
        static::tearDownAfterClass();
    }

    /**
     * @test
     * @depends resendInviteIframeFound
     */
    public function resendInviteIframeNotFound()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->session->visit($this->getUrl() . 'iframe');
        $this->session->wait($this->timeout, "typeof $ !== undefined");
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $fillAddress = '45 Rockefeller Plaza, New York City, NY 10111';
        $this->fillGoogleAddress($fillAddress);
        $this->session->wait($this->timeout, "window.location.pathname.match('\/user\/invite\/[0-9]') != null");
        $this->session->wait($this->timeout, "$('#rentjeeves_publicbundle_invitetenanttype').length > 0");
        $this->assertNotNull($this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype_invite_unitName'));
        $this->assertNotNull($form = $this->page->find('css', '#rentjeeves_publicbundle_invitetenanttype'));
        $this->fillForm(
            $form,
            array(
                'rentjeeves_publicbundle_invitetenanttype_tenant_email' => 'connie@rentrack.com',
            )
        );

        $this->assertNotNull($submit = $this->page->find('css', '#submitForm'));
        $submit->click();
        $this->checkResendInvite();
    }

    /**
     * @test
     */
    public function checkHoldingSelectForNew()
    {
        $this->load(true);
        $this->setDefaultSession('goutte');
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        /** @var Holding $holdingFirst */
        $holdingFirst = $em->getRepository('DataBundle:Holding')->findOneBy(['name' => 'Rent Holding']);

        /** @var Holding $holdingSecond */
        $holdingSecond = $em->getRepository('DataBundle:Holding')->findOneBy(['name' => 'Estate Holding']);

        $this->assertNotNull($holdingFirst);
        $this->assertNotNull($holdingSecond);

        $link1 = sprintf(
            '%suser/new/%d/holding',
            $this->getUrl(),
            $holdingFirst->getId()
        );

        $link2 = sprintf(
            '%suser/new/%d/holding',
            $this->getUrl(),
            $holdingSecond->getId()
        );

        $this->session->visit($link1);
        $this->assertNotNull($thisIsMyRental = $this->page->findAll('css', '.thisIsMyRental'));
        $this->assertEquals(5, count($thisIsMyRental), 'Wrong count of rental property for holding');

        $this->session->visit($link2);
        $this->assertNotNull($thisIsMyRental = $this->page->findAll('css', '.thisIsMyRental'));
        $this->assertEquals(1, count($thisIsMyRental), 'Wrong count of rental property for holding');
    }
}
