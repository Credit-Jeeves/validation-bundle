<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\ImportApiMapping;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;

class ImportMRICase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function mriBaseImport()
    {
        $this->load(true);

        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->findOneByName('Rent Holding');
        $this->assertNotNull($holding, 'We have wrong fixtures');
        $propertyMapping500 = new ImportApiMapping();
        $propertyMapping500->setHolding($holding);
        $propertyMapping500->setCity('Dallas');
        $propertyMapping500->setState('TX');
        $propertyMapping500->setStreet('9951 Acklin Drive');
        $propertyMapping500->setExternalPropertyId(500);
        $this->getEntityManager()->persist($propertyMapping500);
        $this->getEntityManager()->flush();

        $propertyMapping503 = new ImportApiMapping();
        $propertyMapping503->setHolding($holding);
        $propertyMapping503->setCity('Dallas');
        $propertyMapping503->setState('TX');
        $propertyMapping503->setStreet('9945 Acklin Drive');
        $propertyMapping503->setExternalPropertyId(503);
        $this->getEntityManager()->persist($propertyMapping500);
        $this->getEntityManager()->flush();


        $contracts = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findAll();
        $countContracts = count($contracts);

        $contractsInWaitingStatus = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findBy(
            ['status' => ContractStatus::WAITING]
        );
        $countContractsWaiting = count($contractsInWaitingStatus);

        $this->setDefaultSession('selenium2');
        $importGroupSettings = $this->getImportGroupSettings();
        $this->assertNotEmpty($importGroupSettings, 'We do not have correct settings in fixtures');
        $importGroupSettings->setSource(ImportSource::INTEGRATED_API);
        $importGroupSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importGroupSettings->setApiPropertyIds('500,503');
        $importGroupSettings->getGroup()->getHolding()->setAccountingSystem(AccountingSystem::MRI);
        $this->getEntityManager()->flush();

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();

        $this->session->wait(
            1000000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();

        //First page
        $submitImportFile = $this->getDomElement('.submitImportFile>span', 'Can not find submit button');
        $submitImportFile->click();
        $this->waitReviewAndPost();
        //Second page
        $submitImportFile = $this->getDomElement('.submitImportFile>span', 'Can not find submit button');
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->assertGreaterThan(
            $countContracts,
            count($this->getEntityManager()->getRepository('RjDataBundle:Contract')->findAll()),
            'We not save any contracts'
        );


        $this->assertGreaterThan(
            $countContractsWaiting,
            count(
                $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findBy(
                    ['status' => ContractStatus::WAITING]
                )
            ),
            'We not save any contracts in waiting'
        );
    }
}
