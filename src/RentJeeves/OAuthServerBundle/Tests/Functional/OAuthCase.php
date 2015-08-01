<?php

namespace RentJeeves\OAuthServerBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class OAuthCase extends BaseTestCase
{
    const CLIENT_ID = '3_3iui2vbjxtwksskk88os8040408ccwwkss80o4c4c88skoc804';

    /**
     * @test
     */
    public function findTenantByMappedResidentIdAndHolding()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

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

        $this->logout();
    }

    /**
     * @test
     */
    public function findContractWaitingByResidentIdAndHolding()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        /** @var ContractWaiting $contractWaiting */
        $contractWaiting = $this->getEntityManager()
            ->getRepository('RjDataBundle:ContractWaiting')
            ->findOneBy(['residentId' => 't0013535']);

        $this->session->visit(
            sprintf(
                $this->getUrl() .
                'oauth/v2/auth?client_id=%s&resident_id=%s&holding_id=%s&response_type=code',
                self::CLIENT_ID,
                $contractWaiting->getResidentId(),
                $contractWaiting->getGroup()->getHolding()->getId()
            )
        );

        $this->assertNotNull($createUserLink = $this->page->find('css', '#create-user'));

        $createUserLink->click();

        $this->assertNotNull($form = $this->page->find('css', '#registration_form'));

        $this->assertNotNull($firstName = $this->page->find('css', '#api_tenant_first_name'));
        $this->assertEquals($contractWaiting->getFirstName(), $firstName->getValue());

        $this->assertNotNull($lastName = $this->page->find('css', '#api_tenant_last_name'));
        $this->assertEquals($contractWaiting->getLastName(), $lastName->getValue());

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

    protected function logout()
    {
        $this->getMink()->resetSessions();
    }
}
