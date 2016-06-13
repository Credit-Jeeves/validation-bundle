<?php

namespace RentJeeves\ImportBundle\Tests\Unit\LeaseImport\Fields;

use RentJeeves\ImportBundle\LeaseImport\Fields\LeaseFieldBitmap;
use RentJeeves\ImportBundle\LeaseImport\Fields\LeaseFields;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class LeaseFieldBitmapCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldReturnMatchedBitMaskWhenIsMatch()
    {
        $importLeaseFieldManager = new LeaseFieldBitmap(false);
        $this->assertEquals(0xb, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for matched');
    }

    /**
     * @test
     */
    public function shouldReturnNewBitMaskWhenIsNew()
    {
        $importLeaseFieldManager = new LeaseFieldBitmap(true);
        $this->assertEquals(0x1f, $importLeaseFieldManager->getUpdateMask(), 'We should get mask for new');
    }

    /**
     * @test
     */
    public function shouldReturnIntByDiffBitWhenSetRent()
    {
        $importLeaseFieldManager = new LeaseFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(LeaseFields::RENT);
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
        $importLeaseFieldManager = new LeaseFieldBitmap(true);
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::RENT),
            'We should mark different failure when put bit number incorrect'
        );
    }

    /**
     * @test
     */
    public function shouldBeAbleToUpdateWhenPutBitNumber()
    {
        $importLeaseFieldManager = new LeaseFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(LeaseFields::RENT);
        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::RENT),
            'We should mark different successfully when put bit number correct'
        );
    }

    /**
     * @test
     */
    public function shouldMatchNewMaskWhenSetAllFieldsLikeForNewMask()
    {
        $importLeaseFieldManager = new LeaseFieldBitmap(true);
        $importLeaseFieldManager->markDifferent(LeaseFields::RENT);
        $importLeaseFieldManager->markDifferent(LeaseFields::DUE_DATE);
        $importLeaseFieldManager->markDifferent(LeaseFields::FINISH_AT);
        $importLeaseFieldManager->markDifferent(LeaseFields::START_AT);
        $importLeaseFieldManager->markDifferent(LeaseFields::BALANCE);
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
        $importLeaseFieldManager = new LeaseFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(LeaseFields::RENT);
        $importLeaseFieldManager->markDifferent(LeaseFields::FINISH_AT);
        $importLeaseFieldManager->markDifferent(LeaseFields::BALANCE);

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
        $importLeaseFieldManager = new LeaseFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(LeaseFields::RENT);

        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::RENT),
            'We make RENT like different value so we do need update it'
        );
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::START_AT),
            'We don\'t mark start at like different so we don\'t need update it'
        );
    }

    /**
     * @test
     */
    public function whenMaskMatchedShouldAllowToUpdateOnlyMatchedFields()
    {
        $importLeaseFieldManager = new LeaseFieldBitmap(false);
        $importLeaseFieldManager->markDifferent(LeaseFields::RENT);
        $importLeaseFieldManager->markDifferent(LeaseFields::BALANCE);
        $importLeaseFieldManager->markDifferent(LeaseFields::FINISH_AT);
        $importLeaseFieldManager->markDifferent(LeaseFields::START_AT);
        $importLeaseFieldManager->markDifferent(LeaseFields::DUE_DATE);

        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::RENT),
            'We make RENT like different value so we do need update it'
        );
        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::BALANCE),
            'We make BALANCE like different value so we do need update it'
        );
        $this->assertTrue(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::FINISH_AT),
            'We make FINISH_AT like different value so we do need update it'
        );
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::START_AT),
            'We don\'t have start at in mask to update so we don\'t need update it'
        );
        $this->assertFalse(
            $importLeaseFieldManager->isNeedUpdate(LeaseFields::DUE_DATE),
            'We don\'t have due date in mask to update so we don\'t need update it'
        );
    }
}

