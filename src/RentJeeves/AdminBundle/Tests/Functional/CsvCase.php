<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

use RentJeeves\LandlordBundle\Tests\Functional\ImportBaseAbstract;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;

class CsvCase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function shouldCreateNewMapping()
    {
        $this->load(true);
        $this->setDefaultSession('symfony');
        $importMappingChoice = $this->getEntityManager()->getRepository('RjDataBundle:ImportMappingChoice')->findAll();
        $this->assertCount(0, $importMappingChoice, 'We should don\'t have mapping in fixtures');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $groupBlock = $this->getDomElement('#id_block_groups', 'Groups action doesn\'t show');
        $groupBlock->clickLink('link_list');
        $editLink = $this->getDomElement('a:contains("Generic group")', 'Edit link doesn\'t find for group');
        $editLink->click();
        $tabLink = $this->getDomElement('.nav-tabs li>a:contains("Import Defaults")');
        $tabLink->click();

        $this->assertNotEmpty(
            $mappingLink = $this->page->find('css', '#createCsvMappingLink'),
            'Should see link csv mapping'
        );
        $mappingLink->click();
        $this->assertNotNull(
            $attFile = $this->page->find('css', '#upload_csv_file_attachment'),
            'Attach does not exist'
        );
        $filePath = $this->getFixtureFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $this->assertNotEmpty(
            $buttonUpload = $this->page->find('css', '#upload_csv_file_upload'),
            'Should see upload file button'
        );
        $buttonUpload->click();

        $mapFile = $this->mapFile;
        $mapFile[15] = ImportMapping::KEY_TENANT_STATUS;

        $this->fillCsvMapping($mapFile, 15, '#match_file_type_column');
        $this->assertNotEmpty(
            $buttonSave = $this->page->find('css', '#match_file_type_save'),
            'Should see button save mapping'
        );
        $buttonSave->click();
        $this->assertNotEmpty(
            $buttonSave = $this->page->find('css', '.alert-success'),
            'Should see success alert'
        );
        $this->getEntityManager()->clear();
        $importMappingChoiceCurrent = $this->getEntityManager()->getRepository('RjDataBundle:ImportMappingChoice')
            ->findAll();
        $this->assertCount(1, $importMappingChoiceCurrent, 'New mapping should create');
    }
}
