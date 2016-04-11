<?php

namespace RentJeeves\OAuthServerBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class OAuthCase extends BaseTestCase
{
    const CLIENT_ID = '3_3iui2vbjxtwksskk88os8040408ccwwkss80o4c4c88skoc804';

    /**
     * @test
     */
    public function findTenantByMappedResidentIdAndHolding()
    {
        $this->load(true);
        $this->clearEmail();
        $this->setDefaultSession('selenium2');

        /** @var Tenant $tenant */
        $tenant = $this->getEntityManager()
            ->getRepository('RjDataBundle:Tenant')
            ->findOneBy(['email' => 'connie@rentrack.com']);

        $this->assertNotNull($tenant);

        $this->assertCount(1, $tenant->getResidentsMapping());

        $residentMapping = $tenant->getResidentsMapping()[0];

        $this->session->visit(
            sprintf(
                $this->getUrl() .
                'oauth/v2/auth?client_id=%s&resident_id=%s&holding_id=%s&response_type=code',
                self::CLIENT_ID,
                $residentMapping->getResidentId(),
                $residentMapping->getHolding()->getId()
            )
        );

        $this->assertNotNull($createUserLink = $this->page->find('css', '#create-user'));
        $createUserLink->click();

        $this->assertNotNull($form = $this->page->find('css', '#registration_form'));

        $this->assertNotNull($firstName = $this->page->find('css', '#api_tenant_first_name'));
        $this->assertEquals($tenant->getFirstName(), $firstName->getValue());

        $this->assertNotNull($lastName = $this->page->find('css', '#api_tenant_last_name'));
        $this->assertEquals($tenant->getLastName(), $lastName->getValue());

        $this->assertNotNull($email = $this->page->find('css', '#api_tenant_email'));
        $this->assertEquals($tenant->getEmail(), $email->getValue());

        $this->fillForm($form, [
            'api_tenant_password_Password' => 'ghbrjk',
            'api_tenant_password_Verify_Password' => 'ghbrjk',
            'api_tenant_tos' => true,
        ]);

        $this->assertNotNull($connect = $this->page->find('css', '#connectBtn'));

        $connect->click();
        $this->assertNotNull($text = $this->page->find('css', 'h3'));

        $this->assertEquals('authorization.main_info', $text->getText());

        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(0, $emails, 'Should not send any emails if user already exists');

        $this->logout();
    }

    /**
     * @test
     */
    public function findContractWaitingByResidentIdAndHolding()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        /** @var Contract $contractWaiting */
        $contractWaiting = $this->getEntityManager()->find('RjDataBundle:Contract', 3);
        $this->assertNotNull($contractWaiting, 'Contract#3 should exist in DB');
        /** @var Tenant $tenant */
        $tenant = $this->getContainer()->get('renttrack.user_creator')->createTenant('Mike', 'Pool');
        $contractWaiting->setStatus(ContractStatus::WAITING);
        $contractWaiting->setTenant($tenant);
        $residentMapping = new ResidentMapping();
        $residentMapping->setHolding($contractWaiting->getHolding());
        $residentMapping->setTenant($tenant);
        $residentMapping->setResidentId('t0013535');
        $tenant->addResidentsMapping($residentMapping);
        $this->getEntityManager()->persist($tenant);
        $this->getEntityManager()->persist($residentMapping);
        $this->getEntityManager()->flush();

        $this->session->visit(
            sprintf(
                $this->getUrl() .
                'oauth/v2/auth?client_id=%s&resident_id=%s&holding_id=%s&response_type=code',
                self::CLIENT_ID,
                $residentMapping->getResidentId(),
                $contractWaiting->getHolding()->getId()
            )
        );

        $this->assertNotNull($createUserLink = $this->page->find('css', '#create-user'));

        $createUserLink->click();

        $this->assertNotNull($form = $this->page->find('css', '#registration_form'));

        $this->assertNotNull($firstName = $this->page->find('css', '#api_tenant_first_name'));
        $this->assertEquals($tenant->getFirstName(), $firstName->getValue());

        $this->assertNotNull($lastName = $this->page->find('css', '#api_tenant_last_name'));
        $this->assertEquals($tenant->getLastName(), $lastName->getValue());

        $this->fillForm($form, [
            'api_tenant_email' => 'mario.supper.test@gmail.com',
            'api_tenant_password_Password' => 'ghbrjk',
            'api_tenant_password_Verify_Password' => 'ghbrjk',
            'api_tenant_tos' => true,
        ]);

        $this->assertNotNull($connect = $this->page->find('css', '#connectBtn'));

        $connect->click();

        $this->assertNotNull($text = $this->page->find('css', 'h3'));

        $this->assertEquals('authorization.main_info', $text->getText());

        $this->logout();
    }

    /**
     * @test
     */
    public function shouldSendEmailIfUserIsNew()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->clearEmail();

        $this->session->visit(
            sprintf(
                $this->getUrl() .
                'oauth/v2/auth?client_id=%s',
                self::CLIENT_ID
            )
        );

        $this->assertNotNull($createUserLink = $this->page->find('css', '#create-user'));

        $createUserLink->click();

        $this->assertNotNull($form = $this->page->find('css', '#registration_form'));

        $this->assertNotNull($firstName = $this->page->find('css', '#api_tenant_first_name'));
        $firstName->setValue('New tenant');

        $this->assertNotNull($lastName = $this->page->find('css', '#api_tenant_last_name'));
        $lastName->setValue('lastname');

        $this->assertNotNull($email = $this->page->find('css', '#api_tenant_email'));
        $email->setValue('apinewtenant@example.com');

        $this->fillForm($form, [
            'api_tenant_password_Password' => '1111',
            'api_tenant_password_Verify_Password' => '1111',
            'api_tenant_tos' => true,
        ]);

        $this->assertNotNull($connect = $this->page->find('css', '#connectBtn'));

        $connect->click();

        $this->assertNotNull($text = $this->page->find('css', 'h3'));

        $this->assertEquals('authorization.main_info', $text->getText());

        $this->setDefaultSession('goutte');
        $emails = $this->getEmails();
        $this->assertCount(1, $emails, 'Should send email if user does not exist');
        $email = $this->getEmailReader()->getEmail($emails[0]);
        $this->assertEquals(
            'Get Started with RentTrack',
            $email->getHeaders()->get('Subject')->getFieldValue()
        );

        $this->logout();
    }

    protected function logout()
    {
        $this->getMink()->resetSessions();
    }
}
