<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\LandlordBundle\Tests\Functional\ImportBaseAbstract;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;

class CsvMappingCase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function map()
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

        $this->assertNotEmpty($mappingLink = $this->page->find('css', '#createCsvMappingLink'), 'Should see link csv mapping');
        $mappingLink->click();

        $this->assertNotNull(
            $attFile = $this->page->find('css', '#upload_csv_file_attachment'),
            'Attach does not exist'
        );
        $filePath = $this->getFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $this->assertNotEmpty(
            $buttonUpload = $this->page->find('css', '#upload_csv_file_upload'),
            'Should see link csv mapping'
        );
        $buttonUpload->click();

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;

        $this->fillCsvMapping($mapFile, 15, '#match_file_type_column');
        $this->assertNotEmpty(
            $buttonSave = $this->page->find('css', '#match_file_type_save'),
            'Should see link csv mapping'
        );

        $buttonSave->click();
        $this->assertNotEmpty(
            $buttonSave = $this->page->find('css', '.alert-success'),
            'Should see link csv mapping'
        );
    }
}
