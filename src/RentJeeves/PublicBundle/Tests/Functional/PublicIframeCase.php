<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class PublicIframeCase extends BaseTestCase
{
    /**
     * @test
     */
    public function checkTenantAndLandlordButton()
    {
        $this->session->visit($this->getUrl() . 'management');
        $this->assertNotNull($tenantButton = $this->page->find('css', '#iframe-tenant-button'));
        $this->assertNull($landlordButton = $this->page->find('css', '#iframe-landlord-button'));
        $this->session->visit($this->getUrl() . 'management?l=true');
        $this->assertNotNull($tenantButton = $this->page->find('css', '#iframe-tenant-button'));
        $this->assertNotNull($landlordButton = $this->page->find('css', '#iframe-landlord-button'));
    }
}
