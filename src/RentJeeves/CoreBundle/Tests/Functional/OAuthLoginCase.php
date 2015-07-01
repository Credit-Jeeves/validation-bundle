<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class OAuthLoginCase extends BaseTestCase
{
    /**
     * @test
     */
    public function loginTenant()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . '?access_token=test');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
    }

    /**
     * @test
     */
    public function loginLandlord()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . '?access_token=test_landlord');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->assertNotNull($this->page->findAll('css', '#payments-block td'));
    }
}
