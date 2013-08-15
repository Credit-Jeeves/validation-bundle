<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class FirtstPropertyCase extends BaseTestCase
{
    protected $timeout = 20000;

    protected function fillGoogleAddress($fillAddress)
    {
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $propertySearch->setValue($fillAddress);
        $propertySearch->click();
        $this->session->wait($this->timeout, "$('.pac-item').length > 0");
        $this->session->wait($this->timeout, "$('.pac-item').parent().is(':visible')");
        $this->assertNotNull($item = $this->page->find('css', '.pac-item'));
        $item->click();
    }

    /**
     * @test
     */
    public function index()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);
        $this->login('landlord6@example.com', 'pass');
        $this->page->clickLink('tabs.properties');
        $this->session->wait($this->timeout, "$('#property-search').length > 0");
        $this->assertNotNull($addUnit = $this->page->find('css', '#addProperty'));
        $addUnit->click();
        $address = "30 Rockefeller Plaza, New York City, NY 10112";
        $this->fillGoogleAddress('30 Rockefeller Plaza, New York City, NY 10112');
        $this->assertNotNull($numberOfUnit = $this->page->find('css', '#numberOfUnit'));
        $numberOfUnit->setValue(5);
        $this->assertNotNull($addUnit = $this->page->find('css', '#addUnit'));
        $addUnit->click();
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-name'));
        
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');
        $unitNames[3]->setValue('1D');
        $unitNames[4]->setValue('1T');
        
        $this->assertNotNull($addUnit = $this->page->find('css', '#addProperty'));
        $addUnit->click();
        $this->session->wait($this->timeout, '$(".properties-table-block").length > 0');
        $this->session->wait($this->timeout, "!$('.properties-table-block').is(':visible')");
        $this->session->wait($this->timeout, "$('.properties-table-block').is(':visible')");
        $this->assertNotNull($tr = $this->page->find('css', '.properties-table>tbody>tr'));
    }
}
