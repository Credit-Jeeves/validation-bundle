<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

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
        $this->assertCount(2, $this->page->findAll('css', 'select.select-unit>option'));

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

        $user = $this->getEntityManager()->find('RjDataBundle:Tenant', 42);
        $this->assertTrue($user->getEmailNotification(), 'Email notification should be true.');
        $this->assertTrue($user->getOfferNotification(), 'Offer notification should be true.');

        $this->session->visit($this->getUrl() . 'unsub?md_email='. $user->getEmail()); // user 42

        $this->assertEquals(200, $this->session->getStatusCode());

        $this->getEntityManager()->refresh($user);
        $this->assertFalse($user->getEmailNotification(), 'Email notification should be false after UnsubscribePage.');
        $this->assertFalse($user->getOfferNotification(), 'Offer notification should be false after UnsubscribePage.');
    }
}
