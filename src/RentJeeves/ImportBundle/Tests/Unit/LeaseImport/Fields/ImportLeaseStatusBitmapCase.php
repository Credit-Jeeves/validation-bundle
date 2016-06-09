<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\ImportLeaseStatusBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\ImportLeaseStatusFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ImportLeaseStatusBitmapCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function providerForPassedStatusCorrect()
    {
        return [
            [ImportLeaseStatusFields::NEW_ONE],
            [ImportLeaseStatusFields::ERROR],
            [ImportLeaseStatusFields::MATCH],
            [ImportLeaseStatusFields::SKIP],
        ];
    }

    /**
     * @test
     * @dataProvider providerForPassedStatusCorrect
     */
    public function shouldPassedStatusCorrect($bitNumber)
    {
        $importLeaseStatusBitmap = new ImportLeaseStatusBitmap();
        $importLeaseStatusBitmap->setStatus($bitNumber);

        $this->assertTrue(
            $importLeaseStatusBitmap->isStatusSet($bitNumber),
            sprintf('We should have bitNumber# %s in list of bits', $bitNumber)
        );
    }

    /**
     * @test
     */
    public function shouldReturnDiffBitmapWhenAddStatus()
    {
        $importLeaseStatusBitmap = new ImportLeaseStatusBitmap();
        $importLeaseStatusBitmap->setStatus(ImportLeaseStatusFields::NEW_ONE);

        $this->assertEquals(2, $importLeaseStatusBitmap->getStatusBitmap(), 'We should have diffMap 2');
    }
}

