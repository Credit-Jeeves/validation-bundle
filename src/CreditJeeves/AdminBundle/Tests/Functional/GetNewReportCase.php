<?php
namespace CreditJeeves\AdminBundle\Tests\Functional;

use CreditJeeves\TestBundle\Functional\BaseTestCase;

class GetNewReportCase extends BaseTestCase
{
    /**
     * @test
     */
    public function success()
    {
        $this->load(true);
        //$this->setDefaultSession('Selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_applicants'));
        $tableTr->clickLink('link_list');
        $this->assertNotNull($getNewReport = $this->page->findAll('css', 'a.getNewReport'));
        $this->assertCount(25, $getNewReport);
        $getNewReport[5]->click();
        $this->assertNotNull($tableTr = $this->page->find('css', '.alert-success'));
        $this->logout();
    }

    /**
     * @test
     */
    public function info()
    {
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_applicants'));
        $tableTr->clickLink('link_list');
        $this->assertNotNull($getNewReport = $this->page->findAll('css', 'a.getNewReport'));
        $this->assertCount(25, $getNewReport);
        $getNewReport[0]->click();
        $this->assertNotNull($tableTr = $this->page->find('css', '.alert-info'));
        $this->logout();
    }

    /**
     * @test
     */
    public function error()
    {
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableTr = $this->page->find('css', '#id_block_applicants'));
        $tableTr->clickLink('link_list');
        $this->assertNotNull($getNewReport = $this->page->findAll('css', 'a.getNewReport'));
        $this->assertCount(25, $getNewReport);
        $getNewReport[24]->click();
        $this->assertNotNull($tableTr = $this->page->find('css', '.alert-error'));
        $this->logout();
    }
}