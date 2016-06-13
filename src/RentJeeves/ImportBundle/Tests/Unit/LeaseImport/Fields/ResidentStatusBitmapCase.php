<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\ResidentStatusBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\ResidentStatusFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ResidentStatusBitmapCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function providerForPassedStatusCorrect()
    {
        return [
            [ResidentStatusFields::BAD_EMAIL],
            [ResidentStatusFields::NO_EMAIL],
            [ResidentStatusFields::INVITED],
            [ResidentStatusFields::NOT_INVITED],
            [ResidentStatusFields::ERROR],
        ];
    }
    /**
     * @test
     * @dataProvider providerForPassedStatusCorrect
     */
    public function shouldPassedStatusCorrect($bitNumber)
    {
        $importLeaseStatusBitmap = new ResidentStatusBitmap();
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
        $importLeaseStatusBitmap = new ResidentStatusBitmap();
        $importLeaseStatusBitmap->addStatus(ResidentStatusFields::BAD_EMAIL);
        $this->assertEquals(16, $importLeaseStatusBitmap->getDiffBitmap(), 'We should have diffMap 2');
    }
}
