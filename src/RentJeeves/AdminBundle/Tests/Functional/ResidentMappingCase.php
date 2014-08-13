<?php
namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ResidentMappingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function checkCreate()
    {
        $this->load(true);
        $this->setDefaultSession('goutte');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($tableBlock = $this->page->find('css', '#id_block_resident_mapping'));

        $tableBlock->clickLink('link_list');

        $this->assertNotNull($residents = $this->page->findAll('css', 'tbody tr'));
        $this->assertEquals(1, count($residents));

        $this->assertNotNull($create = $this->page->find('css', '.sonata-action-element'));
        $create->click();

        $this->assertNotNull($input = $this->page->findAll('css', 'form input'));
        $this->assertEquals(5, count($input));
        $input[0]->setValue("residentIdyepitsme");

        $this->assertNotNull($btn = $this->page->findAll('css', '.form-actions .btn'));
        $this->assertEquals(3, count($btn));

        $btn[1]->click();

        $this->assertNotNull($residents = $this->page->findAll('css', 'tbody tr'));
        $this->assertEquals(2, count($residents));
    }
}
