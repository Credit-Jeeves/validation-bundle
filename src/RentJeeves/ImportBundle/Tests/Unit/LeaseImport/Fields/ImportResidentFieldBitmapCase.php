<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\ImportResidentFieldBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\ImportResidentFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ImportResidentFieldBitmapCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnNewBitMaskWhenIsNew()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(false);
        $this->assertEquals(0x84, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for new');
    }

    /**
     * @test
     */
    public function shouldReturnMatchedBitMaskWhenIsMatch()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(true);
        $this->assertEquals(0xfe, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for new');
    }

    /**
     * @test
     */
    public function shouldReturnIntByDiffBitWhenSetRent()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::FIRST_NAME);
        $this->assertEquals(
            8,
            $importLeaseFieldManager->getDiffBitmap(),
            'We should mark different successfully when put bit number correct'
        );
    }

    /**
     * @test
     */
    public function shouldBeNotAbleToUpdateWhenPutBitNumber()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(true);
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(ImportResidentFields::EXTERNAL_RESIDENT_ID),
            'We should mark different failure when put bit number incorrect'
        );
    }

    /**
     * @test
     */
    public function shouldBeAbleToUpdateWhenPutBitNumber()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::EXTERNAL_RESIDENT_ID);
        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(ImportResidentFields::EXTERNAL_RESIDENT_ID),
            'We should mark different successfully when put bit number correct'
        );
    }

    /**
     * @test
     */
    public function shouldMatchNewMaskWhenSetAllFieldsLikeForNewMask()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::FIRST_NAME);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::LAST_NAME);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::EXTERNAL_RESIDENT_ID);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::DATE_OF_BIRTH);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::EMAIL);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::PHONE);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::RESIDENT_STATUS);

        $this->assertEquals(
            0xfe,
            $importLeaseFieldManager->getDiffBitmap(),
            'Should be match with mask new'
        );
    }

    /**
     * @test
     */
    public function shouldMatchMatchedMaskWhenSetAllFieldsLikeForMatchedMask()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::EMAIL);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::RESIDENT_STATUS);

        $this->assertEquals(
            0x84,
            $importLeaseFieldManager->getDiffBitmap(),
            'Should be match with mask matched'
        );
    }

    /**
     * @test
     */
    public function shouldMatchBitInMatchedMaskWhenOnlyRentChanged()
    {
        $importLeaseFieldManager = new ImportResidentFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(ImportResidentFields::EMAIL);

        $this->assertTrue($importLeaseFieldManager->isNeedUpdate(ImportResidentFields::EMAIL));
        $this->assertFalse($importLeaseFieldManager->isNeedUpdate(ImportResidentFields::FIRST_NAME));
    }
}

