<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class FirtstPropertyCase extends BaseTestCase
{

    protected function fillGoogleAddress($fillAddress)
    {
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->assertNotNull($propertySearch = $this->page->find('css', '#property-search'));
        $propertySearch->click();
        $this->fillForm(
            $form,
            array(
                'property-search' => $fillAddress,
            )
        );
        $propertySearch->click();
        $this->session->wait(3000);
        $this->assertNotNull($item = $this->page->find('css', '.pac-item'));

        $this->session->executeScript(
            "$('.pac-item').show();"
        );
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
        $address = "30 Rockefeller Plaza, New York City, NY 10112";
        $this->fillGoogleAddress('30 Rockefeller Plaza, New York City, NY 10112');
        $this->assertNotNull($form = $this->page->find('css', '#formSearch'));
        $this->fillForm(
            $form,
            array(
                'numberOfUnit' => 5,
            )
        );

        $this->assertNotNull($addUnit= $this->page->find('css', '#addUnit'));
        $addUnit->click();
        $this->assertNotNull($unitNames = $this->page->findAll('css', '.unit-name'));
        
        $unitNames[0]->setValue('1A');
        $unitNames[1]->setValue('1B');
        $unitNames[2]->setValue('1C');
        $unitNames[3]->setValue('1D');
        $unitNames[4]->setValue('1T');
        
        $this->assertNotNull($addUnit= $this->page->find('css', '#addProperty'));
        $addUnit->click();
        $this->session->wait(8000);
        $this->assertNotNull($tr = $this->page->find('css', '.properties-table>tbody>tr'));
    }
}
