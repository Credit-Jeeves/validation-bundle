<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class PropertyMappingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateAndListPropertyMapping()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->loginByAccessToken(
            'admin@creditjeeves.com',
            $this->getUrl() . 'admin/rentjeeves/data/propertymapping/list'
        );

        $this->assertNotNull($properties = $this->page->findAll('css', '.sonata-ba-list tbody tr'));

        $this->assertCount(3, $properties); // we have 2 tbody last is footer with 1 tr

        $this->assertNotNull($create = $this->page->find('css', '.sonata-action-element'));
        $create->click();
        $this->session->wait(
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            15000,
            "$('.overlay-trigger').length <= 0"
        );
        $this->assertNotNull($input = $this->page->findAll('css', 'form input'));
        $this->assertCount(7, $input);
        $input[0]->setValue(5);
        $input[1]->setValue(3);
        $input[2]->setValue("p987SS13");

        $this->assertNotNull($btn = $this->page->findAll('css', '.form-actions .btn'));
        $this->assertCount(3, $btn);

        $btn[1]->click();

        $this->assertNotNull($properties = $this->page->findAll('css', '.sonata-ba-list tbody tr'));
        $this->assertCount(4, $properties);
    }
}
