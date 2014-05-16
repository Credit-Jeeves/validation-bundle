<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class PropertiesCase extends BaseTestCase
{
    protected $timeout = 20000;

    /**
     * @test
     */
    public function sorting()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($zipCollum = $this->page->find('css', '#zip'));
        $zipCollum->click();
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('50 18th Ave', $firstTd->getText(), 'Wrong notice');
        $this->assertNotNull($zipCollum = $this->page->find('css', '#zip'));
        $zipCollum->click();
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('10 de Octubre', $firstTd->getText(), 'Wrong notice');
        $this->logout();
    }

    /**
     * test
     */
    public function search()
    {
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertNotNull($searchButton = $this->page->find('css', '#searchButton'));
        $searchButton->click();
        $this->assertNotNull($search = $this->page->find('css', '#search'));
        $this->assertNotNull($searchFilterSelectLink = $this->page->find('css', '#searchFilterSelect_link'));
        
        $searchFilterSelectLink->click();
        $this->assertNotNull($searchFilterSelectLinkValue = $this->page->find('css', '#searchFilterSelect_li_1'));
        $searchFilterSelectLinkValue->click();
        $search->setValue('Havana');

        $this->assertNotNull($searchButton = $this->page->find('css', '#searchButton'));
        $searchButton->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($firstTr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(1, $firstTr, 'wrong number of collum');

        $this->assertNotNull($firstTd = $this->page->find('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('10 de Octubre', $firstTd->getText(), 'Wrong notice');
        $this->logout();
    }

    /**
     * test
     */
    public function addProperty()
    {
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($propertyButtonAdd = $this->page->find('css', '.property-button-add'));

        $this->assertNotNull($pages = $this->page->findAll('css', '.pagePagination'));
        $this->assertCount(4, $pages, 'wrong number of collum');
        $pages[3]->click();
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(8, $tr, 'wrong number of collum');

        $propertyButtonAdd->click();
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($saveProperty = $this->page->find('css', '#saveProperty'));
        $saveProperty->click();
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $fillAddress = 'Top of the Rock Observation Deck, Rockefeller Plaza, New York City, NY';
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $propertySearch->setValue($fillAddress);
        $searchSubmit->click();
        $this->session->wait($this->timeout, "!$('.loadingSpinner').is(':visible')");
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

        $this->assertNotNull($saveProperty = $this->page->find('css', '#saveProperty'));
        $saveProperty->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");

        $this->assertNotNull($pages = $this->page->findAll('css', '.pagePagination'));
        $this->assertCount(4, $pages, 'wrong number of collum');
        $pages[3]->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(9, $tr, 'wrong number of collum');
        $this->logout();
    }

    /**
     * @test
     */
    public function addSingleProperty()
    {
        $this->login('landlord2@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($propertyButtonAdd = $this->page->find('css', '.property-button-add'));



        $propertyButtonAdd->click();
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($error = $this->page->find('css', '.single-property-checkbox .error'));
        $this->assertEquals('', $error->getText());
        $this->assertNotNull($unitsBox = $this->page->find('css', '#property-units'));
        $this->assertTrue($unitsBox->isVisible());

        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $fillAddress = 'Top of the Rock Observation Deck, Rockefeller Plaza, New York City, NY';
        $this->assertNotNull($searchSubmit = $this->page->find('css', '#search-submit'));
        $propertySearch->setValue($fillAddress);
        $searchSubmit->click();
        $this->session->wait($this->timeout, "!$('.loadingSpinner').is(':visible')");
        $this->assertNotNull($saveProperty = $this->page->find('css', '#saveProperty'));
        $saveProperty->click();
        $this->assertNotNull($error = $this->page->find('css', '.single-property-checkbox .error'));
        $this->assertEquals('units.error.add_or_mark_single', $error->getText());
        $this->assertNotNull($checkbox = $this->page->find('css', '#isSingleProperty'));
        $checkbox->click();
        $this->session->wait($this->timeout, "!$('#property-units').is(':visible')");
        $this->assertNotNull($unitsBox = $this->page->find('css', '#property-units'));
        $this->assertFalse($unitsBox->isVisible());

        $this->assertNotNull($saveProperty = $this->page->find('css', '#saveProperty'));
        $saveProperty->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");

        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(1, $tr);
        $this->assertNotNull($edit = $tr[0]->find('css', '.property-edit'));
        $this->assertFalse($edit->isVisible());
        $this->assertNotNull($remove = $tr[0]->find('css', '.delete'));
        $this->assertTrue($remove->isVisible());
        $remove->click();
        $this->session->wait($this->timeout, "$('#remove-property-popup').is(':visible')");
        $this->page->pressButton('yep.remove.property');
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $this->assertCount(0, $tr);
        $this->logout();
    }

    /**
     * test
     */
    public function manageUnits()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($propertyEdit = $this->page->find('css', '.property-edit'));
        $propertyEdit->click();
        $this->session->wait($this->timeout, "$('#blockPopupEditProperty').is(':visible')");
        $this->assertNotNull($propertyEdit = $this->page->find('css', '#inputEditAddUnit'));
        $propertyEdit->setValue(150);
        $this->assertNotNull($propertyAdd = $this->page->find('css', '#addEditUnit'));
        $propertyAdd->click();
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-input-edit'));

        for ($i=0; $i < 150; $i++) {
            $unitNames[$i]->setValue($i+1);
        }

        $this->assertNotNull($saveManageUnits = $this->page->find('css', '#saveManageUnits'));
        $saveManageUnits->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($td = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('150', $td[5]->getText(), 'wrong number of unit');

        $this->assertNotNull($propertyEdit = $this->page->find('css', '.property-edit'));
        $propertyEdit->click();
        $this->session->wait($this->timeout, "$('#blockPopupEditProperty').is(':visible')");
        $this->assertNotNull($propertyEdit = $this->page->find('css', '#inputEditAddUnit'));
        $this->assertNotNull($saveManageUnits = $this->page->find('css', '#saveManageUnits'));
        $saveManageUnits->click();
        $this->session->wait($this->timeout, "!$('#blockPopupEditProperty').is(':visible')");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");

        $this->assertNotNull($propertyEdit = $this->page->find('css', '.property-edit'));
        $propertyEdit->click();
        $this->session->wait($this->timeout, "$('#blockPopupEditProperty').is(':visible')");
        $this->assertNotNull($propertyEdit = $this->page->find('css', '#inputEditAddUnit'));
        $this->assertNotNull($saveManageUnits = $this->page->find('css', '#saveManageUnits'));
        $saveManageUnits->click();
        $this->session->wait($this->timeout, "!$('#blockPopupEditProperty').is(':visible')");
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertEquals('150', $td[5]->getText(), 'wrong number of unit');

        $this->logout();
    }

    /**
    * test
    */
    public function removeProperty()
    {
        $this->load(true);
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "!$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($all = $this->page->find('css', '#all'));
        $this->assertEquals('18', $all->getText(), 'wrong number of property');
        $this->assertNotNull($propertyEdit = $this->page->find('css', '.property-edit'));
        $propertyEdit->click();
        $this->session->wait($this->timeout, "$('#blockPopupEditProperty').is(':visible')");
        $this->assertNotNull($removePropertyConfirm = $this->page->find('css', '.removePropertyConfirm'));
        $removePropertyConfirm->click();
        $this->assertNotNull($removeProperyLast = $this->page->find('css', '.removeProperyLast'));
        $removeProperyLast->click();
        $this->session->wait($this->timeout, "$('#processLoading').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertEquals('17', $all->getText(), 'wrong number of property');
        $this->logout();
    }
}
