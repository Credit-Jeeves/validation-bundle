<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\ImportLeaseFieldBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\ImportLeaseFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ImportLeaseFieldBitmapCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnMatchedBitMaskWhenIsMatch()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(false);
        $this->assertEquals(0xb, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for matched');
    }

    /**
     * @test
     */
    public function shouldReturnNewBitMaskWhenIsNew()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(true);
        $this->assertEquals(0x1f, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for new');
    }

    /**
     * @test
     */
    public function shouldReturnIntByDiffBitWhenSetRent()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::RENT);
        $this->assertEquals(
            2,
            $importLeaseFieldManager->getDiffBitmap(),
            'We should mark different successfully when put bit number correct'
        );
    }

    /**
     * @test
     */
    public function shouldBeNotAbleToUpdateWhenPutBitNumber()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(true);
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(ImportLeaseFields::RENT),
            'We should mark different failure when put bit number incorrect'
        );
    }

    /**
     * @test
     */
    public function shouldBeAbleToUpdateWhenPutBitNumber()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::RENT);
        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(ImportLeaseFields::RENT),
            'We should mark different successfully when put bit number correct'
        );
    }

    /**
     * @test
     */
    public function shouldMatchNewMaskWhenSetAllFieldsLikeForNewMask()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::RENT);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::DUE_DATE);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::FINISH_AT);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::START_AT);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::BALANCE);
        $this->assertEquals(
            0x1f,
            $importLeaseFieldManager->getDiffBitmap(),
            'Should be match with mask new'
        );
    }

    /**
     * @test
     */
    public function shouldMatchMatchedMaskWhenSetAllFieldsLikeForMatchedMask()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::RENT);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::FINISH_AT);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::BALANCE);

        $this->assertEquals(
            0xb,
            $importLeaseFieldManager->getDiffBitmap(),
            'Should be match with mask matched'
        );
    }

    /**
     * @test
     */
    public function shouldMatchBitInMatchedMaskWhenOnlyRentChanged()
    {
        $importLeaseFieldManager = new ImportLeaseFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(ImportLeaseFields::RENT);

        $this->assertTrue($importLeaseFieldManager->isNeedUpdate(ImportLeaseFields::RENT));
        $this->assertFalse($importLeaseFieldManager->isNeedUpdate(ImportLeaseFields::START_AT));
    }
}

