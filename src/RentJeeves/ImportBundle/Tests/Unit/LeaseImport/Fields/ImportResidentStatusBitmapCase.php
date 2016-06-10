<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\ImportResidentStatusBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\ImportResidentStatusFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ImportResidentStatusBitmapCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function providerForPassedStatusCorrect()
    {
        return [
            [ImportResidentStatusFields::BAD_EMAIL],
            [ImportResidentStatusFields::NO_EMAIL],
            [ImportResidentStatusFields::INVITED],
            [ImportResidentStatusFields::NOT_INVITED],
            [ImportResidentStatusFields::ERROR],
        ];
    }
    /**
     * @test
     * @dataProvider providerForPassedStatusCorrect
     */
    public function shouldPassedStatusCorrect($bitNumber)
    {
        $importLeaseStatusBitmap = new ImportResidentStatusBitmap();
        $importLeaseStatusBitmap->addStatus($bitNumber);
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
        $importLeaseStatusBitmap = new ImportResidentStatusBitmap();
        $importLeaseStatusBitmap->addStatus(ImportResidentStatusFields::BAD_EMAIL);
        $this->assertEquals(16, $importLeaseStatusBitmap->getDiffBitmap(), 'We should have diffMap 2');
    }
}
