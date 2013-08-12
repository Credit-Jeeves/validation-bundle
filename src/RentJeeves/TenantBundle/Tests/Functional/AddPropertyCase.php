<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class AddPropertyCase extends BaseTestCase
{
    protected $timeout = 20000;

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
        $this->login('tenant11@example.com', 'pass');
    }
}
