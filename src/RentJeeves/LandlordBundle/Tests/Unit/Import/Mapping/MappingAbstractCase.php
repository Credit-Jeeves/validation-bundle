<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Mapping;

use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;


class MappingTest extends MappingAbstract
{

    public function testStreetParse($row)
    {
        return $this->parseStreet($row);
    }

    public function testUnitParse($row)
    {
        return $this->parseUnit($row);
    }

    public function getData($start, $length)
    {
        // stub to implement interface
    }

    public function isSkipped(array $row)
    {
        // stub to implement interface
    }

    public function isNeedManualMapping()
    {
        // stub to implement interface
    }

    public function getTotal()
    {
        // stub to implement interface
    }
}

class MappingAbstractCase extends \PHPUnit_Framework_TestCase
{

    public function streetDataProvider()
    {
        return array(
            array([ "street" => "171 Hester Place"], "171 Hester Place", null),
            array([ "street" => "862 Three Wood Drive"], "862 Three Wood Drive", null),
            array([ "street" => "5013 Yorkchester Drive"], "5013 Yorkchester Drive", null),
            array([ "street" => "959 Chester Circle"], "959 Chester Circle", null),
            array([ "street" => "5029 Yorkchester Dr"], "5029 Yorkchester Dr", null),
            array([ "street" => "101 MAIN ST"], "101 MAIN ST", null),
            array([ "street" => "101 MAIN ST APT 12"], "101 MAIN ST ", "12"),
            array([ "street" => "101 W MAIN ST. APT 12"], "101 W MAIN ST. ", "12"),
            array([ "street" => "101 MAIN ST S.APT.12"], "101 MAIN ST S." , "12"),
            array([ "street" => "101 MAIN ST # 12"], "101 MAIN ST ", "12"),
            array([ "street" => "101 MAIN ST Unit 12"], "101 MAIN ST " , "12"),
            array([ "street" => "101 MAIN ST RM 12"], "101 MAIN ST ", "12"),
            array([ "street" => "101 MAIN ST STE. 12"], "101 MAIN ST ", "12"),
            # array([ "street" => "3875 Taylor Road-205"], "3875 Taylor Road", "205"),
        );
    }

    /**
     * @test
     * @dataProvider streetDataProvider
     */
    public function parseStreet($row, $expected_street, $expected_unit)
    {
        $mapper = new MappingTest();
        $actual = $mapper->testStreetParse($row);
        $this->assertEquals($expected_street, $actual["street"]);
        $actualUnit = array_key_exists("unit", $actual) ? $actual["unit"] : null;
        $this->assertEquals($expected_unit, $actualUnit);
    }

    public function unitDataProvider()
    {
        return array(
            array([ "street" => "171 Hester Place", "unit" => "" ], "171 Hester Place", null),
            array([ "street" => "862 Three Wood Drive", "unit" => "123" ], "862 Three Wood Drive", "123"),
            array([ "street" => "5013 Yorkchester Drive", "unit" => "Ste.4" ], "5013 Yorkchester Drive", "4"),
            array([ "street" => "959 Chester Circle", "unit" => "unit A" ], "959 Chester Circle", "A"),
            array([ "street" => "5029 Yorkchester Dr", "unit" => "RM 1"], "5029 Yorkchester Dr", "1"),
            array([ "street" => "101 MAIN ST", "unit" => "xyz"], "101 MAIN ST", "xyz"),
            array([ "street" => "101 MAIN ST", "unit" => "APT 12"], "101 MAIN ST", "12"),
            array([ "street" => "101 MAIN ST S.", "unit" => "APT.12"], "101 MAIN ST S." , "12"),
            array([ "street" => "101 MAIN ST", "unit" => "# 12"], "101 MAIN ST", "12"),
            array([ "street" => "101 MAIN ST", "unit" => "Unit 12"], "101 MAIN ST", "12"),
            array([ "street" => "101 MAIN ST", "unit" => "RM 12"], "101 MAIN ST", "12"),
            array([ "street" => "101 MAIN ST", "unit" => "STE. 12"], "101 MAIN ST", "12"),
            # array([ "street" => "3875 Taylor Road-205"], "3875 Taylor Road", "205"),
        );
    }


    /**
     * @test
     * @dataProvider unitDataProvider
     */
    public function parseUnit($row, $expected_street, $expected_unit)
    {
        $mapper = new MappingTest();
        $actual = $mapper->testUnitParse($row);
        $this->assertEquals($expected_street, $actual["street"]);
        $actualUnit = array_key_exists("unit", $actual) ? $actual["unit"] : null;
        $this->assertEquals($expected_unit, $actualUnit);
    }
}
