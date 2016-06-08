<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use RentJeeves\CoreBundle\Services\Bitmap;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class BinMapCase extends UnitTestBase
{

    /**
     * @test
     */
    public function setAndGetBitShouldWorkCorrectly()
    {
        $bitMap = new Bitmap();
        $bitMap->setBit(3);
        $this->assertEquals(1000, base_convert($bitMap->getInt(), 10, 2), 'Bit set incorrectly');
        $bitMap->setBit(2);
        $this->assertEquals(1100, base_convert($bitMap->getInt(), 10, 2), 'Bit set incorrectly');
    }

    /**
     * @test
     */
    public function isBitSetShouldWorkCorrectly()
    {
        $bitMap = new Bitmap();
        $currentHex = dechex($bitMap->getInt());
        $this->assertEquals(0, $currentHex, 'Should be equals to 0');
        $bitMap->setBit(2);
        $bitMap->setBit(4);
        $this->assertTrue($bitMap->isBitSet(2), 'Bit 2 should isset');
        $this->assertTrue($bitMap->isBitSet(4), 'Bit 4 should isset');
        $this->assertFalse($bitMap->isBitSet(1), 'Bit 1 should not isset');
        $this->assertFalse($bitMap->isBitSet(0), 'Bit 0 should not isset');
        $this->assertFalse($bitMap->isBitSet(12), 'Bit 12 should not isset');
    }

    /**
     * @test
     */
    public function isBitSetWithinMaskShouldWorkCorrectly()
    {
        $bitMap = new Bitmap();
        $bitMap->setBit(2);
        $bitMap->setBit(3);
        $this->assertTrue($bitMap->isBitSetWithinMask(2, '100'), 'Bit should isset');
    }

    /**
     * @test
     */
    public function unsetBitsByMaskShouldWorkCorrectly()
    {
        $bitMap = new Bitmap();
        $bitMap->setBit(3);
        $bitMap->setBit(2);
        $this->assertEquals(1100, base_convert($bitMap->getInt(), 10, 2), 'Bit set incorrectly');
        $bitMap->unsetBitsByMask(100); //100 equals to 4 in decimal
        $this->assertFalse($bitMap->isBitSet(2), 'We should don\'t contains this bit - we removed it');
        $this->assertTrue($bitMap->isBitSet(3), 'We should contain this bit');
    }
}
