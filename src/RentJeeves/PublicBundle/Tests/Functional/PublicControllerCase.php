<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class PublicControllerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturn400IfHoldingIdIsWrong()
    {
        $this->load(true);
        $this->session->visit($this->getUrl() . '?holding_id=WRONG&resident_id=t0013534');

        $this->assertNotNull($createAccount = $this->page->find('css', '#create-user'));
        $createAccount->click();

        $this->assertEquals(400, $this->session->getStatusCode());
        $this->assertContains('Holding not found', $this->page->getContent());
    }

    /**
     * @test
     */
    public function shouldReturn400IfHoldingIdDoesNotExist()
    {
        $this->load(true);
        $this->session->visit($this->getUrl() . '?resident_id=t0013534');

        $this->assertNotNull($createAccount = $this->page->find('css', '#create-user'));
        $createAccount->click();

        $this->assertEquals(400, $this->session->getStatusCode());
        $this->assertContains('Holding not found', $this->page->getContent());
    }

    /**
     * @test
     */
    public function shouldRedirectToTenantInviteIfResidentFoundAndHasInvite()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->session->visit($this->getUrl() . '?holding_id=5&resident_id=t0013534'); // user 42
        $user = $this->getEntityManager()->getRepository('RjDataBundle:Tenant')->find(42);
        $code = $user->getInviteCode();

        $this->assertNotNull($createAccount = $this->page->find('css', '#create-user'));
        $createAccount->click();

        $this->assertEquals($this->getUrl() . 'tenant/invite/' . $code, $this->session->getCurrentUrl());
    }

    /**
     * @test
     */
    public function shouldRedirectToLoginAndShowErrorIfResidentFoundAndNoInvite()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $holding = $this->getEntityManager()
            ->getRepository('DataBundle:Holding')->find(5);
        $resident = $this->getEntityManager()
            ->getRepository('RjDataBundle:ResidentMapping')
            ->findOneResidentByHoldingAndResidentId($holding, 't0013534');
        $tenant = $resident->getTenant();
        $tenant->setInviteCode(null);

        $this->getEntityManager()->flush($tenant);

        $this->assertNotNull($resident);

        $this->session->visit($this->getUrl() . '?holding_id=5&resident_id=t0013534'); // user 42
        $this->assertNotNull($createAccount = $this->page->find('css', '#create-user'));
        $createAccount->click();

        $this->assertEquals($this->getUrl() . 'login', $this->session->getCurrentUrl());
        $this->assertEquals(
            'new.user.error.without_invite_code',
            $this->page->find('css', '.login-error')->getText()
        );
    }

    /**
     * @test
     */
    public function shouldSetValuesIfResidentNotFoundButFoundContractWaiting()
    {
        set_time_limit(0); //@TODO fixed fatal: Maximum execution time of 120 seconds exceeded
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $holding = $this->getEntityManager()
            ->getRepository('DataBundle:Holding')->find(5);
        $contracts = $this->getEntityManager()
            ->getRepository('RjDataBundle:ContractWaiting')
            ->findAllByHoldingAndResidentId($holding, 't0013535');

        $this->assertEquals(1, count($contracts));
        $contract = $contracts[0];

        $this->session->visit($this->getUrl() . '?holding_id=5&resident_id=t0013535');
        $this->assertNotNull($createAccount = $this->page->find('css', '#create-user'));
        $createAccount->click();

        $this->assertCount(1, $this->page->findAll('css', 'li.addressText'));
        $this->assertCount(1, $this->page->findAll('css', 'select.select-unit'));
        $this->assertCount(3, $this->page->findAll('css', 'select.select-unit>option'));

        $this->assertEquals(
            $contract->getFirstName(),
            $this->page->find('css', '#rentjeeves_publicbundle_tenanttype_first_name')->getValue()
        );
        $this->assertEquals(
            $contract->getLastName(),
            $this->page->find('css', '#rentjeeves_publicbundle_tenanttype_last_name')->getValue()
        );
    }

    /**
     * @test
     */
    public function shouldTurnOffEmailNotificationAndOfferNotificationAfterUnsubscribePage()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');

        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        $this->assertTrue($user->getEmailNotification(), 'Email notification should be true.');
        $this->assertTrue($user->getOfferNotification(), 'Offer notification should be true.');

        $this->session->visit($this->getUrl() . 'unsub?md_email='. $user->getEmail()); // user 42

        $this->assertEquals(200, $this->session->getStatusCode());

        $this->getEntityManager()->refresh($user);
        $this->assertFalse($user->getEmailNotification(), 'Email notification should be false after UnsubscribePage.');
        $this->assertFalse($user->getOfferNotification(), 'Offer notification should be false after UnsubscribePage.');
    }

    /**
     * @test
     */
    public function shouldShowSuccessfulPageIfEmailIsUnknown()
    {
        $this->session->visit($this->getUrl() . 'unsub?md_email=12345');
        $this->assertEquals(200, $this->session->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldFilterPropertiesByHoldingIfHoldingIsSpecified()
    {
        $this->markTestSkipped('Unskipp after RT-1860');
        $this->load(true);
        $this->setDefaultSession('goutte');
        $em = $this->getEntityManager();

        /** @var Holding $holdingFirst */
        $holdingFirst = $em->getRepository('DataBundle:Holding')->findOneBy(['name' => 'Rent Holding']);

        /** @var Holding $holdingSecond */
        $holdingSecond = $em->getRepository('DataBundle:Holding')->findOneBy(['name' => 'Estate Holding']);

        $this->assertNotNull($holdingFirst, 'Rent Holding not found');
        $this->assertNotNull($holdingSecond, 'Estate Holding not found');

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
        $this->assertNotNull(
            $thisIsMyRental = $this->page->findAll('css', '.thisIsMyRental'),
            'ThisIsMyRental not found for Rent Holding'
        );
        $this->assertEquals(5, count($thisIsMyRental), 'Wrong count of rental property for Rent Holding');

        $this->session->visit($link2);
        $this->assertNotNull(
            $thisIsMyRental = $this->page->findAll('css', '.thisIsMyRental'),
            'ThisIsMyRental not found for Estate Holding'
        );
        $this->assertEquals(1, count($thisIsMyRental), 'Wrong count of rental property for Estate Holding');
    }

    /**
     * @test
     */
    public function shouldFilterPropertiesByGroupIfGroupIsSpecified()
    {
        $this->load(true);
        $this->setDefaultSession('goutte');
        $em = $this->getEntityManager();

        /** @var Group $groupFirst */
        $groupFirst = $em->find('DataBundle:Group', 24);

        /** @var Group $groupSecond */
        $groupSecond = $em->find('DataBundle:Group', 26);

        $this->assertNotNull($groupFirst, 'Group #24 not found');
        $this->assertNotNull($groupSecond, 'Group #26 not found');

        $link1 = sprintf(
            '%suser/new/%d/group',
            $this->getUrl(),
            $groupFirst->getId()
        );

        $link2 = sprintf(
            '%suser/new/%d/group',
            $this->getUrl(),
            $groupSecond->getId()
        );

        $this->session->visit($link1);
        $this->assertNotNull(
            $thisIsMyRental = $this->page->findAll('css', '.thisIsMyRental'),
            'ThisIsMyRental not found for Group #24'
        );
        $this->assertEquals(19, count($thisIsMyRental), 'Wrong count of rental property for Group #24');

        $this->session->visit($link2);
        $this->assertNotNull(
            $thisIsMyRental = $this->page->findAll('css', '.thisIsMyRental'),
            'ThisIsMyRental not found for Group #26'
        );
        $this->assertEquals(1, count($thisIsMyRental), 'Wrong count of rental property for Group #26');
    }

    /**
     * @test
     */
    public function shouldAllowTenantSignUpWithNotMatchedFirstLastNamesIfAllowPayAnythingEnabledAndGroupIntegrated()
    {
        $propertyId = 19;
        $unitId = 27;

        $this->load(true);
        $em = $this->getEntityManager();
        $unit = $em->find('RjDataBundle:Unit', $unitId);
        /** @var Unit $unit */
        $this->assertNotNull($unit, 'Unit #27 should exist');
        $this->assertTrue($unit->getProperty()->isSingle(), 'Property should be SINGLE');
        $this->assertEquals($propertyId, $unit->getProperty()->getId(), 'Unit #27 should belong to single property#19');
        $groupSettings = $unit->getGroup()->getGroupSettings();
        $groupSettings->setIsIntegrated(true);
        $em->flush($groupSettings);
        $this->assertFalse($groupSettings->isAllowPayAnything(), 'PayAnything should be disabled');

        $contractWaiting = new ContractWaiting();
        $contractWaiting->setUnit($unit);
        $contractWaiting->setProperty($unit->getProperty());
        $contractWaiting->setGroup($unit->getGroup());
        $contractWaiting->setFirstName('Mark');
        $contractWaiting->setLastName('Totti');
        $contractWaiting->setRent(111);
        $contractWaiting->setResidentId('r548787');
        $contractWaiting->setStartAt(new \DateTime());
        $contractWaiting->setFinishAt(new \DateTime());
        $em->persist($contractWaiting);
        $em->flush($contractWaiting);
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneByUnit($unit);
        $this->assertNull($contract, 'Contract with unit #27 should not exist');

        $this->setDefaultSession('selenium2');
        $this->session->visit(
            sprintf('%suser/new/%s', $this->getUrl(), $propertyId)
        );
        $thisIsMyRentBtn = $this->page->find('css', 'button.thisIsMyRental');
        $this->assertNotNull($thisIsMyRentBtn, 'ThisIsMyRental button not found');
        $thisIsMyRentBtn->click();
        $newUserForm = $this->page->find('css', '#formNewUser');
        $this->fillForm(
            $newUserForm,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'Tomas', // set not matched name
                'rentjeeves_publicbundle_tenanttype_last_name'  => 'Totti',
                'rentjeeves_publicbundle_tenanttype_email' => 'tomas_totti@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            ]
        );
        $this->page->pressButton('continue');
        $this->session->wait($this->timeout, '$(".error_list").length > 0');
        $errorList = $this->page->findAll('css', '.error_list');
        $this->assertCount(1, $errorList, 'One error expected');
        $this->assertEquals('error.unit.reserved', $errorList[0]->getText(), 'Error should be "error.unit.reserved"');

        $groupSettings->setAllowPayAnything(true);
        $em->flush($groupSettings);

        $thisIsMyRentBtn = $this->page->find('css', 'button.thisIsMyRental');
        $this->assertNotNull($thisIsMyRentBtn, 'ThisIsMyRental button not found');
        $thisIsMyRentBtn->click();
        $newUserForm = $this->page->find('css', '#formNewUser');
        $this->fillForm(
            $newUserForm,
            [
                'rentjeeves_publicbundle_tenanttype_first_name' => 'Frank', // set not matched name
                'rentjeeves_publicbundle_tenanttype_last_name'  => 'Gaudi',
                'rentjeeves_publicbundle_tenanttype_email' => 'frank_gaudi@mail.com',
                'rentjeeves_publicbundle_tenanttype_password_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_password_Verify_Password' => '123',
                'rentjeeves_publicbundle_tenanttype_tos' => true,
            ]
        );
        $this->page->pressButton('continue');
        $this->session->wait(60000, '$("h3.title:contains(\'verify.email\')").length > 0');
        $this->assertContains('/new/send/', $this->session->getCurrentUrl(), 'Location should be /new/send/');

        /** @var Contract $contract */
        $contract = $em->getRepository('RjDataBundle:Contract')->findOneByUnit($unit);
        $this->assertNotNull($contract, 'Contract with unit #27 should be created from waiting!');
        $this->assertEquals(ContractStatus::PENDING, $contract->getStatus(), 'Contract should be created as PENDING');
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneByUnit($unit);
        $this->assertNotNull($contractWaiting, 'ContractWaiting with unit #27 should remain in the DB');
    }
}
