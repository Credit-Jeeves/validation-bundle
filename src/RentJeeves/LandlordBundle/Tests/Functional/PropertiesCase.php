<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class PropertiesCase extends BaseTestCase
{

    /**
     * @test
     */
    public function sorting()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait(3000, null);
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('Vía Fernandez', $firstTd->getText(), 'Wrong notice');
        $this->assertNotNull($zipCollum = $this->page->find('css', '#zip'));
        $zipCollum->click();
        $this->session->wait(3000, null);
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('50 18th Ave', $firstTd->getText(), 'Wrong notice');
        $this->assertNotNull($zipCollum = $this->page->find('css', '#zip'));
        $zipCollum->click();
        $this->session->wait(3000, null);
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('10 de Octubre', $firstTd->getText(), 'Wrong notice');
        $this->logout();
    }

    /**
     * @test
     */
    public function search()
    {
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait(3000, null);
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));

        $this->assertNotNull($search = $this->page->find('css', '#search'));
        $this->assertNotNull($searchFilterSelectLink = $this->page->find('css', '#searchFilterSelect_link'));
        
        $searchFilterSelectLink->click();
        $this->assertNotNull($searchFilterSelectLinkValue = $this->page->find('css', '#searchFilterSelect_li_2'));
        $searchFilterSelectLinkValue->click();
        $search->setValue('Havana');

        $this->assertNotNull($searchButton = $this->page->find('css', '#searchButton'));
        $searchButton->click();
        $this->session->wait(3000, null);
        $this->assertNotNull($firstTr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(1, $firstTr, 'wrong number of collum');

        $this->assertNotNull($firstTr = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('10 de Octubre', $firstTd->getText(), 'Wrong notice');
    }
}
