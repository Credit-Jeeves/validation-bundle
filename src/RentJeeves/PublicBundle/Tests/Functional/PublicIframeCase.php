<?php
namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class PublicIframeCase extends BaseTestCase
{
    protected $timeout = 30000;

    /**
     * @test
     */
    public function loginViaIframe()
    {
        //$this->setDefaultSession('selenium2');
        $this->load(true);
        $this->session->visit($this->getUrl() . 'management');
        $this->assertNotNull($form = $this->page->find('css', '#iframe-login-form form'));
        $this->fillForm(
            $form,
            array(
                '_username'    => 'tenant11@example.com',
                '_password' => 'pass',
            )
        );
        $this->assertNotNull($submit = $this->page->find('css', '#save'));
        $submit->click();
        $this->markTestIncomplete('FINISH');
//         $this->session->wait($this->timeout, "$('.properties-table>tbody>tr').children().length > 0");
//         $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
//         $this->assertCount(3, $tr, 'List of property');
        //$this->logout();
    }

    /**
     * @test
     */
    public function checkTenantAndLandlordButton()
    {
        $this->setDefaultSession('selenium2');
        $this->session->visit($this->getUrl() . 'management');
        $this->assertNotNull($tenantButton = $this->page->find('css', '#iframe-tenant-button'));
        $this->assertNull($landlordButton = $this->page->find('css', '#iframe-landlord-button'));
        $this->session->visit($this->getUrl() . 'management?l=true');
        $this->assertNotNull($tenantButton = $this->page->find('css', '#iframe-tenant-button'));
        $this->assertNotNull($landlordButton = $this->page->find('css', '#iframe-landlord-button'));
    }
}
