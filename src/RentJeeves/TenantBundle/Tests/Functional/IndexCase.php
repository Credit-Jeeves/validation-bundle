<?php

namespace RentJeeves\TenantBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class IndexCase extends BaseTestCase
{
    /**
     * @test
     */
    public function existPaymentsHistory()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('tenant11@example.com', 'pass');
        $this->assertNotNull($paymentsTable = $this->page->find('css', '#tenant-payments'));
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(10, count($payments));
        $this->assertNotNull($pages = $this->page->find('css', '.pagination-box'));
        $this->assertEquals('1 2 3 4', $pages->getText());

        $this->assertNotNull($filterPayments_link = $this->page->find('css', '#selContract_link'));
        $filterPayments_link->click();
        $this->assertNotNull($contract1 = $this->page->find('css', '#selContract_li_1'));
        $this->assertNotNull($noDataTitle = $this->page->find('css', '.notHaveData'));
        $this->assertFalse($noDataTitle->isVisible());
        $contract1->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertTrue($noDataTitle->isVisible());

        $filterPayments_link->click();
        $this->assertNotNull($contract2 = $this->page->find('css', '#selContract_li_2'));
        $contract2->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(10, count($payments));
        $this->assertNotNull($pages = $this->page->find('css', '.pagination-box'));
        $this->assertEquals('1 2', $pages->getText());
        $this->assertNotNull($pageLinks = $this->page->findAll('css', '.pagination-box>ul>li>a'));
        $pageLinks[count($pageLinks) - 1]->click();
        $this->session->wait($this->timeout, "$('.overlay').is(':visible')");
        $this->session->wait($this->timeout, "!$('.overlay').is(':visible')");
        $this->assertNotNull($payments = $this->page->findAll('css', '#tenant-payments table>tbody>tr'));
        $this->assertEquals(2, count($payments));
        $this->logout();
    }
}
