<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class CsvMappingCase extends BaseTestCase
{
    /**
     * @test
     */
    public function editContract()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');

        $editLink = $this->getDomElement('a:contains("Generic group")', 'Edit link doesn\'t find for group');
        $editLink->click();
        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Import Defaults")');
        $tabLink->click();
    }
}
