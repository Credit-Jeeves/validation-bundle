<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class TenantCase extends BaseTestCase
{
    /**
     * @test
     */
    public function index()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
    }
}
