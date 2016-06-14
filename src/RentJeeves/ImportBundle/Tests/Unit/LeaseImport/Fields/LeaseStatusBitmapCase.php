<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\LeaseStatusBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\LeaseStatusFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class LeaseStatusBitmapCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function providerForPassedStatusCorrect()
    {
        return [
            [LeaseStatusFields::NEW_ONE],
            [LeaseStatusFields::ERROR],
            [LeaseStatusFields::MATCH],
            [LeaseStatusFields::SKIP],
        ];
    }

    /**
     * @test
     * @dataProvider providerForPassedStatusCorrect
     */
    public function shouldPassedStatusCorrect($bitNumber)
    {
        $importLeaseStatusBitmap = new LeaseStatusBitmap();
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
        $importLeaseStatusBitmap = new LeaseStatusBitmap();
        $importLeaseStatusBitmap->setStatus(LeaseStatusFields::NEW_ONE);

        $this->assertEquals(2, $importLeaseStatusBitmap->getStatusBitmap(), 'We should have diffMap 2');
    }
}
