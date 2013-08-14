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
    public function approve()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(2, $contractPendings, 'Wrong number of pending');
        $this->assertNotNull($approve = $this->page->find('css','.approve'));
        $approve->click();
        $this->page->pressButton('approve.tenant');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(1, $contractPendings, 'Wrong number of pending');
        $this->logout();
    }

    /**
     * @test
     */
    public function sort()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Timothy applegate', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#tenant'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('William johnson', $td[1]->getText(), 'Wrong text in field');

        $this->assertNotNull($tenant = $this->page->find('css', '#tenant'));
        $tenant->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', 'td'));
        $this->assertEquals('Alex jordan', $td[1]->getText(), 'Wrong text in field');
        $this->logout();
    }

    /**
     * @test
     */
    public function edit()
    {
/*        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $currentUrl = $this->session->getCurrentUrl();
        $this->page->clickLink('tabs.tenants');
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(2, $contractPendings, 'Wrong number of pending');
        $this->assertNotNull($approve = $this->page->find('css','.approve'));
        $approve->click();
        $this->page->pressButton('edit.Info');*/
//@TODO not finished yet
/*        $session->evaluateScript(
    "(function(){ return 'something from browser'; })()"
);*/



/*        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->assertNotNull($propertiesTable = $this->page->find('css', '.properties-table'));
        $this->assertNotNull($contractPendings = $this->page->findAll('css', '.contract-pending'));
        $this->assertCount(1, $contractPendings, 'Wrong number of pending');
        $this->logout();*/
    }
}
