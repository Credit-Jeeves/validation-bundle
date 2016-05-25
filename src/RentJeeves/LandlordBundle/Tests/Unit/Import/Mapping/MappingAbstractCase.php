<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Mapping;

use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;
use \DateTime;

class MappingAbstractCase extends \PHPUnit_Framework_TestCase
{
    protected static $countDateFormat = 0;

    public function dataProviderForCheckDateFormat()
    {
        return array(
            array('02/04/2014', '2014-02-04', 'm/d/Y'),
            array('2014-02-04', '2014-02-04', 'Y-m-d'),
            array('20140204', '2014-02-04', 'Ymd'),
            array('02042014', '2014-02-04', 'dmY'),
            array('2014-02-04', '2014-02-04', 'mdY'),
            array('2014/02/04', '2014-02-04', 'Y/m/d'),
            array('4/2/14', '2014-02-04', 'j/n/y'),
            array('04/02/14', '2014-02-04', 'd/m/y'),
            array('02/04/14', '2014-02-04', 'm/d/y'),
            array('02-04-14', '2014-02-04', 'm-d-y'),
            array('02-04-2014', '2014-02-04', 'm-d-Y'),
            array('2-4-2014', '2014-02-04', 'n-j-Y'),
            array('2-4-14', '2014-02-04', 'n-j-y'),
            array('April 2, 2014', '2014-02-04', 'F d, Y'),
            array('04-Apr-14', '2014-02-04', 'd-M-y'),
        );
    }

    /**
     * This test check ImportMapping::$mappingDates for make sure all format correct
     *
     * @test
     * @dataProvider dataProviderForCheckDateFormat
     */
    public function checkDateFormat($dateStringForParse, $dateStringForCheck, $format)
    {
        static::$countDateFormat++;
        DateTime::createFromFormat($dateStringForParse, $format);
    }

    /**
     * @test
     * @depends checkDateFormat
     */
    public function checkCountDateFormat()
    {
        $this->assertEquals(
            count(MappingAbstract::$mappingDates),
            static::$countDateFormat,
            'Some date format not check, please add it into data dataProviderForDateFormat'
        );
    }

    public function streetDataProvider()
    {
        return array(
            array(['street' => '171 Hester Place'], '171 Hester Place', null),
            array(['street' => '862 Three Wood Drive'], '862 Three Wood Drive', null),
            array(['street' => '5013 Yorkchester Drive'], '5013 Yorkchester Drive', null),
            array(['street' => '959 Chester Circle'], '959 Chester Circle', null),
            array(['street' => '5029 Yorkchester Dr'], '5029 Yorkchester Dr', null),
            array(['street' => '101 MAIN ST'], '101 MAIN ST', null),
            array(['street' => '101 MAIN ST APT 12'], '101 MAIN ST', '12'),
            array(['street' => '101 W MAIN ST. APT 12'], '101 W MAIN ST', '12'),
            array(['street' => '101 MAIN ST S.APT.12'], '101 MAIN ST S', '12'),
            array(['street' => '101 MAIN ST # 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST Unit 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST RM 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST STE. 12'], '101 MAIN ST', '12'),
            array(['street' => '2715 P Street #2'], '2715 P Street', '2'),
            array(['street' => '2715 P Street #11'], '2715 P Street', '11'),
            array(['street' => '2715 P Street #11'], '2715 P Street', '11'),
            array(['street' => '527-F SPRING FOREST'], '527 SPRING FOREST', 'F'),
            # below case checks for matching 'rm' in 'farm'
            array(['street' => '826 Davenport Farm Road'], '826 Davenport Farm Road', null),
            # failed for '-' within unit name
            array(['street' => '3903 #PP-5 STERLING'], '3903 STERLING', 'PP-5'),
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
        $this->assertEquals($expected_street, $actual['street']);
        $actualUnit = array_key_exists('unit', $actual) ? $actual['unit'] : null;
        $this->assertEquals($expected_unit, $actualUnit);
    }

    public function unitDataProvider()
    {
        return array(
            array(['street' => '171 Hester Place', 'unit' => ''], '171 Hester Place', null),
            array(['street' => '862 Three Wood Drive', 'unit' => '123'], '862 Three Wood Drive', '123'),
            array(['street' => '5013 Yorkchester Drive', 'unit' => 'Ste.4'], '5013 Yorkchester Drive', '4'),
            array(['street' => '959 Chester Circle', 'unit' => 'unit A'], '959 Chester Circle', 'A'),
            array(['street' => '5029 Yorkchester Dr', 'unit' => 'RM 1'], '5029 Yorkchester Dr', '1'),
            array(['street' => '101 MAIN ST', 'unit' => 'xyz'], '101 MAIN ST', 'xyz'),
            array(['street' => '101 MAIN ST', 'unit' => 'APT 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST S.', 'unit' => 'APT.12'], '101 MAIN ST S.', '12'),
            array(['street' => '101 MAIN ST', 'unit' => '# 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST', 'unit' => 'Unit 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST', 'unit' => 'RM 12'], '101 MAIN ST', '12'),
            array(['street' => '101 MAIN ST', 'unit' => 'STE. 12'], '101 MAIN ST', '12'),
            array(['street' => '2715 P Street #2', 'unit' => 'P2715 #2'], '2715 P Street', '2'),
            array(['street' => '2715 P Street #11', 'unit' => 'P2715#11'], '2715 P Street ', '11'),
            array(['street' => '5105-A Deveron Street', 'unit' => '5105-A'], '5105 Deveron Street', 'A'),
            array(['street' => '3903 #PP-5 STERLING', 'unit' => '3903 #PP-5 STERLING'], '3903 STERLING', 'PP-5'),
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
        $this->assertEquals($expected_street, $actual['street']);
        $actualUnit = array_key_exists('unit', $actual) ? $actual['unit'] : null;
        $this->assertEquals($expected_unit, $actualUnit);
    }

    /**
     * @test
     */
    public function shouldCheckEmail()
    {
        $mapping = new MappingTest();
        $handlerTestReflection = new \ReflectionClass($mapping);
        $mappingMethodForCheckEmail = $handlerTestReflection->getMethod('getEmailFromRow');
        $mappingMethodForCheckEmail->setAccessible(true);
        $row = [MappingAbstract::KEY_EMAIL => 'none@example.com'];

        $resultRow = $mappingMethodForCheckEmail->invoke($mapping, $row);
        $this->assertEmpty($resultRow);

        $row = [MappingAbstract::KEY_EMAIL => 'none@gmail.com'];

        $resultRow = $mappingMethodForCheckEmail->invoke($mapping, $row);
        $this->assertEquals('none@gmail.com', $resultRow);

        $row = [MappingAbstract::KEY_EMAIL => ''];

        $resultRow = $mappingMethodForCheckEmail->invoke($mapping, $row);
        $this->assertEmpty($resultRow);
    }

    /**
     * @test
     */
    public function shouldUseStreetNameAndStreetNumberWhenCreateProperty()
    {
        $data = [
            MappingTest::KEY_CITY => 'Yii',
            MappingTest::KEY_STREET_NAME => 'Yii street',
            MappingTest::KEY_STREET_NUMBER => 1234,
            MappingTest::KEY_ZIP => 'zip',
            MappingTest::KEY_STATE => 'state'
        ];

        $mapping = new MappingTest();
        $property = $mapping->createProperty($data);
        $propertyAddress = $property->getPropertyAddress();

        $this->assertEquals($data[MappingTest::KEY_CITY], $propertyAddress->getCity());
        $this->assertEquals($data[MappingTest::KEY_STREET_NAME], $propertyAddress->getStreet());
        $this->assertEquals($data[MappingTest::KEY_STREET_NUMBER], $propertyAddress->getNumber());
        $this->assertEquals($data[MappingTest::KEY_ZIP], $propertyAddress->getZip());
        $this->assertEquals($data[MappingTest::KEY_STATE], $propertyAddress->getState());
    }

    /**
     * @test
     */
    public function shouldUseStreetWhenCreateProperty()
    {
        $data = [
            MappingTest::KEY_CITY => 'Yii',
            MappingTest::KEY_STREET => 'Yii street',
            MappingTest::KEY_ZIP => 'zip',
            MappingTest::KEY_STATE => 'state'

        ];
        $mapping = new MappingTest();
        $property = $mapping->createProperty($data);
        $propertyAddress = $property->getPropertyAddress();

        $this->assertEquals($data[MappingTest::KEY_CITY], $propertyAddress->getCity());
        $this->assertEquals($data[MappingTest::KEY_STREET], $propertyAddress->getStreet());
        $this->assertEmpty($propertyAddress->getNumber(), 'Number not empty, but should');
        $this->assertEquals($data[MappingTest::KEY_ZIP], $propertyAddress->getZip());
        $this->assertEquals($data[MappingTest::KEY_STATE], $propertyAddress->getState());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Fields for address mapping should be specified, we have only: city,zip,state
     * @test
     */
    public function shouldGetExceptionWhenCreatePropertyWithoutAnyStreetData()
    {
        $data = [
            MappingTest::KEY_CITY => 'Yii',
            MappingTest::KEY_ZIP => 'zip',
            MappingTest::KEY_STATE => 'state'

        ];
        $mapping = new MappingTest();
        $mapping->createProperty($data);
    }

    /**
     * @return array
     */
    public function sanitizeTenantNameProvider()
    {
        return [
            ['Bob Damian and Marley Denial', 'Bob', 'Damian'],
            ['Bob & Damian & Marley', 'Bob', 'Marley'],
            ['Bob Damian & Marley Denial', 'Bob', 'Damian'],
            ['Jerry J. Garcia', 'Jerry', 'Garcia'],
            ['Martin Luther King Jr.', 'Martin', 'King'],
            ['Dr. Ruth Westheimer', 'Ruth', 'Westheimer'],
            ['Bob & Damian Marley', 'Bob', 'Marley'],
            ['Jerry J. Garcia J.', 'Jerry', 'Garcia'],
            ['Bob Damian Marley Denial', 'Bob', 'Denial'],
            ['Bob and Damian Marley', 'Bob', 'Marley'],
        ];
    }

    /**
     * @test
     * @dataProvider sanitizeTenantNameProvider
     *
     * @param string $name
     * @param string $expectedFirstName
     * @param string $expectedLastName
     */
    public function sanitizeTenantName($name, $expectedFirstName, $expectedLastName)
    {
        $mapping = new MappingTest();
        $data = $mapping::parseName($name);
        $this->assertCount(2, $data, 'We should get two element');
        $this->assertEquals(
            $expectedFirstName,
            $data[MappingTest::FIRST_NAME_TENANT],
            'First name mapped not correctly'
        );
        $this->assertEquals($expectedLastName, $data[MappingTest::LAST_NAME_TENANT], 'Last name mapped not correctly');
    }
}
