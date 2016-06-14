<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Bitmap;

use RentJeeves\CoreBundle\Bitmap\Bitmap;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class BitmapCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldSetPassedBitsCorrectly()
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
    public function shouldCheckIfRequiredBitIsSet()
    {
        $bitMap = new Bitmap();
        $currentHex = dechex($bitMap->getInt());
        $this->assertEquals(0, $currentHex, 'Should be equals to 0');
        $bitMap->setBit(2);
        $bitMap->setBit(4);
        $this->assertTrue($bitMap->isBitSet(2), 'Bit 2 should be isset');
        $this->assertTrue($bitMap->isBitSet(4), 'Bit 4 should be isset');
        $this->assertFalse($bitMap->isBitSet(1), 'Bit 1 should not isset');
        $this->assertFalse($bitMap->isBitSet(0), 'Bit 0 should not isset');
        $this->assertFalse($bitMap->isBitSet(12), 'Bit 12 should not isset');
    }

    /**
     * @test
     */
    public function shouldCheckIfBitIsSetWithinMask()
    {
        $bitMap = new Bitmap();
        $bitMap->setBit(2);
        $bitMap->setBit(3);
        $this->assertTrue($bitMap->isBitSetWithinMask(2, '100'), 'Bit should be isset');
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
        $this->assertFalse($bitMap->isBitSet(2), 'The map should not contain');
        $this->assertTrue($bitMap->isBitSet(3), 'The map should contain');
    }
}
