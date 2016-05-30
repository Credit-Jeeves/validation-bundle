<?php

namespace RentJeeves\ImportBundle\Tests\Functional\LeaseImport\Transformer;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use RentJeeves\DataBundle\Enum\ImportLeaseResidentStatus;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ImportBundle\LeaseImport\Transformer\CsvTransformer;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class CsvTransformerCase extends BaseTestCase
{
    /**
     * @test
     * @expectedException \RentJeeves\ImportBundle\Exception\ImportTransformerException
     * @expectedExceptionMessage Input array should contain not empty "hashHeader".
     */
    public function shouldGetExceptionWhenHashHeaderMissed()
    {
        $leaseTransformer = $this->getCsvTransformer();
        $leaseTransformer->transformData([], new Import());
    }

    /**
     * @test
     */
    public function shouldTransformDataAndCheck()
    {
        $this->load(true);
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $importMappingChoice = new ImportMappingChoice();
        $importMappingChoice->setHeaderHash($hash = 'test_hash');
        $importMappingChoice->setGroup($em->getRepository('DataBundle:Group')->find(24));
        $mapping = [
            '1' => 'resident_id',
            '2' => 'tenant_name',
            '3' => 'rent',
            '4' => 'balance',
            '5' => 'unit_id',
            '6' => 'street',
            '8' => 'unit',
            '9' => 'city',
            '10' => 'state',
            '11' => 'zip',
            '13' => 'move_in',
            '14' => 'lease_end',
            '15' => 'move_out',
            '16' => 'month_to_month',
            '17' => 'email',
            '18' => 'group_account_number'
        ];
        $importMappingChoice->setMappingData($mapping);
        $em->persist($importMappingChoice);
        $accountingSystemData = [
            'hashHeader' => $hash,
            'data' => [
                [
                    'residentID',
                    'David Osborn',
                    '560.00',
                    '400.00',
                    'hi_I_am_unit_id',
                    'Street',
                    'none',
                    'Unit',
                    'City',
                    'State',
                    'Zip',
                    'none',
                    '25/10/2015',
                    '25/10/2018',
                    '',
                    'Y',
                    'good@email.com',
                    '12345786'
                ]
            ]
        ];
        $import = new Import();
        $import->setGroup($importMappingChoice->getGroup());
        $importMappingChoice->getGroup()->getImportSettings()->setCsvDateFormat('d/m/Y');
        $import->setUser($em->getRepository('RjDataBundle:Landlord')->findOneByEmail('landlord1@example.com'));
        $import->setImportType(ImportModelType::LEASE);
        $import->setStatus(ImportStatus::RUNNING);
        $em->persist($import);
        $em->flush();
        $this->getCsvTransformer()->transformData($accountingSystemData, $import);
        $em->refresh($import);
        $importLeases = $import->getImportLeases();
        $this->assertTrue($importLeases->offsetExists(0), 'We should transform one record');
        $importLease = $importLeases[0];
        $this->assertEquals(
            $import->getGroup()->getGroupSettings()->getDueDate(),
            $importLease->getDueDate(),
            'DueDate should be the same as in group settings'
        );

        $this->assertEquals('David', $importLease->getFirstName(), 'First Name should be David');
        $this->assertEquals('Osborn', $importLease->getLastName(), 'Last Name should be Osborn');
        $this->assertEquals(560.00, $importLease->getRent(), 'Rent should be 560');
        $this->assertEquals(400.00, $importLease->getIntegratedBalance(), 'Balance should be 400');
        $this->assertEquals('good@email.com', $importLease->getTenantEmail(), 'Email should be good@email.com');
        $this->assertEquals(
            'hi_I_am_unit_id',
            $importLease->getExternalUnitId(),
            'ExternalUnitId should be equals to hi_I_am_unit_id'
        );

        $this->assertInstanceOf('\DateTime', $importLease->getFinishAt(), 'FinishAt Should be mapped');
        $this->assertInstanceOf('\DateTime', $importLease->getStartAt(), 'StartAt Should be mapped');

        $this->assertEquals(
            '25/10/2018',
            $importLease->getFinishAt()->format('d/m/Y'),
            'FinishAt object should be with correct data'
        );
        $this->assertEquals(
            '25/10/2015',
            $importLease->getStartAt()->format('d/m/Y'),
            'StartAt object should be with correct data'
        );

        $this->assertEquals(
            ImportLeaseResidentStatus::CURRENT,
            $importLease->getResidentStatus(),
            'Current status should be here'
        );
        $this->assertNotEquals(
            $import->getGroup()->getId(),
            $importLease->getGroup()->getId(),
            'Group should be mapped to different by account_number'
        );
    }

    protected function getCsvTransformer()
    {
        return new CsvTransformer(
            $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            $this->getContainer()->get('monolog.logger.lease_import')
        );
    }
}

