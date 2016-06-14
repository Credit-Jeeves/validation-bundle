<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\ResidentFieldBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\ResidentFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class ResidentFieldBitmapCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnNewBitMaskWhenIsNew()
    {
        $importLeaseFieldManager = new ResidentFieldBitmap(false);
        $this->assertEquals(0x84, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for new');
    }

    /**
     * @test
     */
    public function shouldReturnMatchedBitMaskWhenIsMatch()
    {
        $importLeaseFieldManager = new ResidentFieldBitmap(true);
        $this->assertEquals(0xfe, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for new');
    }

    /**
     * @test
     */
    public function shouldReturnIntByDiffBitWhenSetRent()
    {
        $importLeaseFieldManager = new ResidentFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ResidentFields::FIRST_NAME);
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
        $importLeaseFieldManager = new ResidentFieldBitmap(true);
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(ResidentFields::EXTERNAL_RESIDENT_ID),
            'We should mark different failure when put bit number incorrect'
        );
    }

    /**
     * @test
     */
    public function shouldBeAbleToUpdateWhenPutBitNumber()
    {
        $importLeaseFieldManager = new ResidentFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ResidentFields::EXTERNAL_RESIDENT_ID);
        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(ResidentFields::EXTERNAL_RESIDENT_ID),
            'We should mark different successfully when put bit number correct'
        );
    }

    /**
     * @test
     */
    public function shouldMatchNewMaskWhenSetAllFieldsLikeForNewMask()
    {
        $importLeaseFieldManager = new ResidentFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(ResidentFields::FIRST_NAME);
        $importLeaseFieldManager->markDifferent(ResidentFields::LAST_NAME);
        $importLeaseFieldManager->markDifferent(ResidentFields::EXTERNAL_RESIDENT_ID);
        $importLeaseFieldManager->markDifferent(ResidentFields::DATE_OF_BIRTH);
        $importLeaseFieldManager->markDifferent(ResidentFields::EMAIL);
        $importLeaseFieldManager->markDifferent(ResidentFields::PHONE);
        $importLeaseFieldManager->markDifferent(ResidentFields::RESIDENT_STATUS);

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
        $importLeaseFieldManager = new ResidentFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(ResidentFields::EMAIL);
        $importLeaseFieldManager->markDifferent(ResidentFields::RESIDENT_STATUS);

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
        $importLeaseFieldManager = new ResidentFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(ResidentFields::EMAIL);

        $this->assertTrue($importLeaseFieldManager->isNeedUpdate(ResidentFields::EMAIL));
        $this->assertFalse($importLeaseFieldManager->isNeedUpdate(ResidentFields::FIRST_NAME));
    }
}
