<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord;

use RentJeeves\TestBundle\BaseTestCase;

class LandlordCsvImporterCase extends BaseTestCase
{
    /**
     * @test
     *
     * @expectedException \Exception
     * @expectedExceptionMessage File "/badFileName.csv" not found.
     */
    public function shouldThrowExceptionIfFileNotFound()
    {
        $partner = $this->getEntityManager()->find('RjDataBundle:Partner', 1);
        $this->getLandlordImporter()->importPartnerLandlords('/badFileName.csv', $partner);
    }

    /**
     * @test
     */
    public function shouldCreateLandlordAndRelatedEntitiesFor2RowsAndReturnErrorFor1Row()
    {
        $this->load(true);

        $partner = $this->getEntityManager()->find('RjDataBundle:Partner', 1);
        $importer = $this->getLandlordImporter();
        /** Before import */
        $allLandlords = $this->getEntityManager()->getRepository('RjDataBundle:Landlord')->findAll();
        $this->assertCount(7, $allLandlords);
        $partnerToUsers = $this->getEntityManager()->getRepository('RjDataBundle:PartnerUserMapping')->findBy(
            ['partner' => $partner]
        );
        $this->assertCount(1, $partnerToUsers);
        $allGroups = $this->getEntityManager()->getRepository('DataBundle:Group')->findAll();
        $this->assertCount(32, $allGroups);
        $allHoldings = $this->getEntityManager()->getRepository('DataBundle:Holding')->findAll();
        $this->assertCount(8, $allHoldings);
        $allProperties = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findAll();
        $this->assertCount(20, $allProperties);
        $allUnits = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findAll();
        $this->assertCount(33, $allUnits);

        $importer->importPartnerLandlords(__DIR__ . '/../../../Fixtures/importFile.csv', $partner);
        $errors = $importer->getMappingErrors();

        /** After import +2 for all Entities */
        $this->assertEquals(1, count($errors));
        $this->assertEquals('[Landlord] email : This value is not a valid email address.', $errors[0]['message']);

        $allLandlords = $this->getEntityManager()->getRepository('RjDataBundle:Landlord')->findAll();
        $this->assertCount(9, $allLandlords);
        $partnerToUsers = $this->getEntityManager()->getRepository('RjDataBundle:PartnerUserMapping')->findBy(
            ['partner' => $partner]
        );
        $this->assertCount(3, $partnerToUsers);
        $allGroups = $this->getEntityManager()->getRepository('DataBundle:Group')->findAll();
        $this->assertCount(34, $allGroups);
        $allHoldings = $this->getEntityManager()->getRepository('DataBundle:Holding')->findAll();
        $this->assertCount(10, $allHoldings);
        $allProperties = $this->getEntityManager()->getRepository('RjDataBundle:Property')->findAll();
        $this->assertCount(22, $allProperties);
        $allUnits = $this->getEntityManager()->getRepository('RjDataBundle:Unit')->findAll();
        $this->assertCount(35, $allUnits);
    }

    /**
     * @return \RentJeeves\LandlordBundle\Accounting\ImportLandlord\LandlordCsvImporter
     */
    protected function getLandlordImporter()
    {
        return $this->getContainer()->get('accounting.landlord_import.importer');
    }
}
