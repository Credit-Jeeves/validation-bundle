<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class OAuthLoginCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldLoginTenantByAccessToken()
    {
        $this->setDefaultSession('goutte');
        $this->load(true);
        $this->session->visit($this->getUrl() . '?access_token=test');
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
    }

    /**
     * @test
     */
    public function shouldLoginLandlordByAccessToken()
    {
        $this->setDefaultSession('goutte');
        $this->load(true);
        $this->session->visit($this->getUrl() . '?access_token=test_landlord');
        $this->assertNotNull($this->page->findAll('css', '#payments-block td'));
    }
}
