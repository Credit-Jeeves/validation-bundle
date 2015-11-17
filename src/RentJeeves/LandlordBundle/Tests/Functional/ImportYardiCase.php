<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

class ImportYardiCase extends ImportBaseAbstract
{

    /**
     * @return array
     */
    public function dataProviderForYardiMultiProperty()
    {
        return [
            [
                $firstCount = 5,
                $secondCount = 7,
                $status = 'import.status.waiting',
                $isNeedLoadFixtures = true,
                $countCountractsCashEquivalent = 0,
                $countCountractsDontAcceptEquivalent = 0,
                $countCountractsExternalLeaseId = 1,
                $residentMappingCount = 0
            ],
            [
                $firstCount = 9,
                $secondCount = 9,
                $status = 'import.status.match',
                $isNeedLoadFixtures = false,
                $countCountractsCashEquivalent = 2,
                $countCountractsDontAcceptEquivalent = 1,
                $countCountractsExternalLeaseId = 3,
                $residentMappingCount = 1
            ]
        ];
    }

    /**
     * @param integer $firstCount
     * @param integer $secondCount
     * @param string $status
     * @param boolean $isNeedLoadFixtures
     * @param integer $countCountractsCashEquivalent ,
     * @param integer $countCountractsDontAcceptEquivalent ,
     * @param integer $countCountractsExternalLeaseId
     * @param integer $residentMappingCount
     *
     * @test
     * @dataProvider dataProviderForYardiMultiProperty
     */
    public function yardiMultiPropertyImport(
        $firstCount,
        $secondCount,
        $status,
        $isNeedLoadFixtures,
        $countCountractsCashEquivalent,
        $countCountractsDontAcceptEquivalent,
        $countCountractsExternalLeaseId,
        $residentMappingCount
    ) {
        $this->setDefaultSession('selenium2');
        $this->load($isNeedLoadFixtures);

        $em = $this->getEntityManager();

        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy([
            'paymentAccepted' => PaymentAccepted::CASH_EQUIVALENT,
        ]);

        $this->assertCount($countCountractsCashEquivalent, $contracts);

        $contractsWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findBy([
            'paymentAccepted' => PaymentAccepted::DO_NOT_ACCEPT,
        ]);

        $this->assertCount($countCountractsDontAcceptEquivalent, $contractsWaiting);

        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy([
            'externalLeaseId' => 't0012020',
        ]);

        $this->assertCount($countCountractsExternalLeaseId, $contracts);

        $residentMapping = $em->getRepository('RjDataBundle:ResidentMapping')->findBy([
            'residentId' => 'r0004169',
        ]);

        $this->assertCount($residentMappingCount, $residentMapping);

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $submitImport->click();

        $this->session->wait(
            3000000,
            "$('table').is(':visible')"
        );

        $trs = $this->getParsedTrsByStatus();
        $this->assertCount($firstCount, $trs[$status], sprintf('%s contract is wrong number', $status));
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $trs = $this->getParsedTrsByStatus();
        $this->assertCount($secondCount, $trs[$status], sprintf('%s contract is wrong number', $status));
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();

        $this->waitRedirectToSummaryPage();
        $this->logout();

        $contract = $em->getRepository('RjDataBundle:Contract')->findOneBy([
            'paymentAccepted' => PaymentAccepted::CASH_EQUIVALENT,
        ]);

        $this->assertNotEmpty($contract);

        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findOneBy([
            'paymentAccepted' => PaymentAccepted::DO_NOT_ACCEPT,
        ]);

        $this->assertNotEmpty($contractWaiting);

        $contractWaitings = $em->getRepository('RjDataBundle:ContractWaiting')->findBy([
            'residentId' => 't0011982',
        ]);
        $this->assertCount(1, $contractWaitings);

        $contracts = $em->getRepository('RjDataBundle:Contract')->findBy([
            'externalLeaseId' => 't0012020',
        ]);

        $this->assertCount(3, $contracts);

        $residentMapping = $em->getRepository('RjDataBundle:ResidentMapping')->findOneBy([
            'residentId' => 'r0004169',
        ]);

        $this->assertNotEmpty($residentMapping);
    }

    /**
     * @test
     * @depends yardiMultiPropertyImport
     */
    public function yardiBaseImportOnlyException()
    {
        $contracts = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->findBy([
            'externalLeaseId' => 't0012020',
        ]);
        $this->assertCount(3, $contracts);
        $this->arrayHasKey(1, $contracts, 'Contracts should have key.');
        $contracts[1]->setRent(0);
        $this->getEntityManager()->flush();
        $this->setDefaultSession('selenium2');
        /** @var Landlord $landlord */
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->session->wait(5000, "typeof jQuery != 'undefined'");
        $this->assertNotNull($submitImport = $this->page->find('css', '.submitImportFile'));
        $this->assertNotNull($exceptionOnly = $this->page->find('css', '#import_file_type_onlyException'));
        $exceptionOnly->check();

        $submitImport->click();

        $this->waitRedirectToSummaryPage();
        $this->getEntityManager()->refresh($contracts[1]);
        $rent = $contracts[1]->getRent();
        $this->assertGreaterThan(1, $rent, sprintf('Should be update, but got %s', $rent));
        $this->getEntityManager()->flush();
    }
}
