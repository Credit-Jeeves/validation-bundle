<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;

class ImportAMSICase extends ImportBaseAbstract
{
    /**
     * @test
     */
    public function shouldImportAMSI()
    {
        $this->load(true);
        // prepare fixtures
        $em = $this->getEntityManager();

        $em->getConnection()
            ->prepare(
                file_get_contents(
                    $this->getFileLocator()->locate('@LandlordBundle/Tests/Fixtures/AMSI_rj_smarty_streets_cache.sql')
                )
            )
            ->execute();

        /** @var Landlord $landlord */
        $landlord = $em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com');
        $this->assertNotNull($landlord, 'Check fixtures, landlord with email "landlord1@example.com" should exist');
        $holding = $landlord->getHolding();
        $holding->setAccountingSystem(AccountingSystem::AMSI);
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByName('Test Rent Group');
        $this->assertNotNull($group, 'Check fixtures, group with name "Test Rent Group" should exist');
        $this->assertEquals(
            $holding->getId(),
            $group->getHolding()->getId(),
            'Check fixtures, group with name "Test Rent Group" should belong to holding with id ' . $holding->getId()
        );
        $importSettings = $group->getImportSettings();
        $importSettings->setApiPropertyIds('001');
        $importSettings->setImportType(ImportType::MULTI_PROPERTIES);
        $importSettings->setSource(ImportSource::INTEGRATED_API);
        $em->flush();

        // We must make sure the data saved into DB, so we count before import and after
        $contract = $em->getRepository('RjDataBundle:Contract')->findAll();
        $this->assertCount(26, $contract, 'Check fixtures, should be present just 26 contracts on DB');
        $this->setDefaultSession('selenium2');
        $this->loginByAccessToken('landlord1@example.com', $this->getUrl() . 'landlord/accounting/import/file');
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $submitImport = $this->getDomElement('.submitImportFile', 'Submit button should exist');
        $submitImport->click();
        $this->session->wait(
            80000,
            "$('table').is(':visible')"
        );
        $this->waitReviewAndPost();
        //First page
        $submitImport->click();
        $this->waitReviewAndPost();
        //Second page
        $submitImport->click();
        $this->waitReviewAndPost();

        // We must make sure the data saved into DB, so we count before import and after
        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy(['externalLeaseId' => 21]);
        $this->assertCount(2, $contracts);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
