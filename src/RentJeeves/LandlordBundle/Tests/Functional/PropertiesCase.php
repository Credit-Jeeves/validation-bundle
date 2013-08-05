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

    /**
     * @test
     */
    public function addProperty()
    {
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait(3000, null);
        $this->assertNotNull($propertyButtonAdd = $this->page->find('css', '.property-button-add'));

        $this->assertNotNull($pages = $this->page->findAll('css', '.page'));
        $this->assertCount(4, $pages, 'wrong number of collum');
        $pages[3]->click();
        $this->session->wait(3000, null);
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(8, $tr, 'wrong number of collum');

        $propertyButtonAdd->click();
        $this->session->wait(1000, null);
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $fillAddress = 'New York Homestay, West 42nd Street, Manhattan, New York City, NY';
        $propertySearch->setValue($fillAddress);
        $propertySearch->click();
        $this->session->wait(3000, null);
        $this->assertNotNull($item = $this->page->find('css', '.pac-item'));
        $item->click();
        $this->assertNotNull($numberOfUnit = $this->page->find('css', '#numberOfUnit'));
        $numberOfUnit->setValue(5);
        $this->assertNotNull($addUnit = $this->page->find('css', '#addUnitToNewProperty>span'));
        $addUnit->click();
        $this->assertNotNull($unitNames = $this->page->findAll('css', '#add-property-popup .unit-name'));
        
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');
        $unitNames[3]->setValue('1D');
        $unitNames[4]->setValue('1T');

        $this->assertNotNull($saveProperty = $this->page->find('css', '#saveProperty>span'));
        $saveProperty->click();
        $this->session->wait(6000, null);

        $this->assertNotNull($pages = $this->page->findAll('css', '.page'));
        $this->assertCount(4, $pages, 'wrong number of collum');
        $pages[3]->click();
        $this->session->wait(3000, null);
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(9, $tr, 'wrong number of collum');

    }

    /**
     * @test
     */
    public function manageUnits()
    {
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait(5000, null);
        $this->assertNotNull($propertyEdit = $this->page->find('css', '.property-edit'));
        $propertyEdit->click();
        $this->session->wait(4000, null);
        $this->assertNotNull($propertyEdit = $this->page->find('css', '#inputEditAddUnit'));
        $propertyEdit->setValue(5);
        $this->assertNotNull($propertyAdd = $this->page->find('css', '#addEditUnit'));
        $propertyAdd->click();
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-input-edit'));
        
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');
        $unitNames[3]->setValue('1D');
        $unitNames[4]->setValue('1T');

        $this->assertNotNull($saveManageUnits = $this->page->find('css', '#saveManageUnits'));
        $saveManageUnits->click();
        $this->session->wait(5000, null);
        $this->assertNotNull($td = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('5', $td[5]->getText(), 'wrong number of unit');
    }
}
