<?php

namespace RentJeeves\ComponentBundle\Tests;

use RentJeeves\ComponentBundle\FileReader\CsvFileReader;

class CsvFileReaderCase extends \PHPUnit_Framework_TestCase
{
    protected static $reportFilename;

    public static function setUpBeforeClass()
    {
        self::$reportFilename = __DIR__ . '/Fixtures/report.csv';
    }
    /**
     * @test
     */
    public function readWithHeader()
    {
        $fileReader = new CsvFileReader();
        $result = $fileReader->read(self::$reportFilename);

        $this->assertEquals(9, count($result));
        $this->assertArrayNotHasKey(0, $result);
        $this->assertEquals('Payment Return', $result[3]['TransactionType']);
        $this->assertEquals('-482.00', $result[3]['AmountAppliedToBill']);
        $this->assertEquals('Mark', $result[3]['PayorFirstName']);
        $this->assertEquals('Pressler', $result[3]['PayorLastName']);
    }

    /**
     * @test
     */
    public function readWithoutHeader()
    {
        $fileReader = new CsvFileReader();
        $fileReader->setUseHeader(false);

        $result = $fileReader->read(self::$reportFilename);

        $this->assertEquals(10, count($result));
        $this->assertArrayHasKey(0, $result);
        $this->assertEquals('MerchantName', $result[0][0]);
        $this->assertEquals('Payment Return', $result[3][2]);
        $this->assertEquals('-482.00', $result[3][6]);
        $this->assertEquals('Mark', $result[3][7]);
        $this->assertEquals('Pressler', $result[3][8]);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function shouldThrowExceptionWhenFileNotFound()
    {
        $fileReader = new CsvFileReader();
        $fileReader->read('unknown/file.ext');
    }
}
