<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResidentMappingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateNewResidentMapping()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->loginByAccessToken(
            'admin@creditjeeves.com',
            $this->getUrl() . 'admin/rentjeeves/data/residentmapping/list'
        );
        $this->assertNotNull($residents = $this->page->findAll('css', 'tbody tr'));
        $this->assertEquals(6, count($residents));

        $this->assertNotNull($create = $this->page->find('css', '.sonata-action-element'));
        $create->click();
        $this->session->wait(15000, "$('.overlay-trigger').length <= 0");

        $this->assertNotNull($input = $this->page->findAll('css', 'form input'));
        $this->assertEquals(6, count($input));

        $input[0]->setValue(5);
        $this->session->wait(15000, "$('.overlay-trigger').length <= 0");
        $input[1]->setValue("residentIdyepitsme");

        $this->assertNotNull($btn = $this->page->findAll('css', '.form-actions .btn'));
        $this->assertEquals(3, count($btn));

        $btn[1]->click();

        $this->assertNotNull($residents = $this->page->findAll('css', 'tbody tr'));
        $this->assertEquals(7, count($residents));
    }
}
