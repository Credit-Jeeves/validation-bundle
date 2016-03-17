<?php

namespace RentJeeves\LandlordBundle\Tests\Functional\Controller;

use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ScanningControllerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldFindAndShowContracts()
    {
        $this->load(true);
        $landlord = $this->getEntityManager()->find('RjDataBundle:Landlord', 65);
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setMerchantId('test');
        $profitStarsSettings->setHolding($landlord->getHolding());

        $this->getEntityManager()->persist($profitStarsSettings);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');

        $this->session->visit($this->getUrl() . 'landlord/scanning/');

        $rows = $this->page->findAll('css', 'tbody>tr');
        $this->assertCount(0, $rows, 'Table should be empty if filters are empty');

        $nameFilter = $this->getDomElement('#lease_finder_name', 'Filter for tenant name not found');
        $emailFilter = $this->getDomElement('#lease_finder_email', 'Filter for tenant email not found');
        $addressFilter = $this->getDomElement('#lease_finder_address', 'Filter for contract address not found');
        $unitFilter = $this->getDomElement('#lease_finder_unit', 'Filter for unit number not found');
        $searchButton = $this->getDomElement('.search-button', 'Search button not found');

        $nameFilter->setValue('Joh');
        $searchButton->click();

        $this->session->wait(3000, "$('#loading-gif').is(':hidden')");

        $rows = $this->page->findAll('css', 'tbody>tr');
        $this->assertCount(3, $rows, 'Table should contain 3 rows');

        $emailFilter->setValue('john@rentrack.com');
        $searchButton->click();
        $this->session->wait(3000, "$('#loading-gif').is(':hidden')");

        $rows = $this->page->findAll('css', 'tbody>tr');
        $this->assertCount(2, $rows, 'Table should contain 2 rows');

        $nameFilter->setValue('');
        $emailFilter->setValue('');
        $addressFilter->setValue('Broad');
        $searchButton->click();
        $this->session->wait(3000, "$('#loading-gif').is(':hidden')");

        $rows = $this->page->findAll('css', 'tbody>tr');
        $this->assertCount(18, $rows, 'Table should contain 18 rows');

        $unitFilter->setValue('234');
        $searchButton->click();
        $this->session->wait(3000, "$('#loading-gif').is(':hidden')");

        $rows = $this->page->findAll('css', 'tbody>tr');
        $this->assertCount(0, $rows, 'Table should contain 0 rows');
    }

    /**
     * @test
     */
    public function shouldShowMessageIfAddressFilterContainShortWord()
    {
        $this->load(true);
        $landlord = $this->getEntityManager()->find('RjDataBundle:Landlord', 65);
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setMerchantId('test');
        $profitStarsSettings->setHolding($landlord->getHolding());

        $this->getEntityManager()->persist($profitStarsSettings);
        $this->getEntityManager()->flush();

        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');

        $this->session->visit($this->getUrl() . 'landlord/scanning/');

        $errorMessage = $this->getDomElement('div.error-message>span');
        $this->assertEmpty($errorMessage->getText(), 'Error message should be empty');

        $emailFilter = $this->getDomElement('#lease_finder_email', 'Filter for tenant email not found');
        $searchButton = $this->getDomElement('.search-button', 'Search button not found');

        $emailFilter->setValue('@');
        $searchButton->click();

        $errorMessage = $this->getDomElement('div.lease-lookup .error-message>span');
        $this->assertNotEmpty($errorMessage->getText(), 'Error message should contain message');
    }
}
