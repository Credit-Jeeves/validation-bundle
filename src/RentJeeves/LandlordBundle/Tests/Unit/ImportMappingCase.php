<?php
namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\LandlordBundle\Accounting\ImportMapping;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use \DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ImportMappingCase extends BaseTestCase
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
            array('4/2/14', '2014-02-04','j/n/y'),
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
        $date = DateTime::createFromFormat($dateStringForParse, $format);
    }

    /**
     * @test
     * @depends checkDateFormat
     */
    public function checkCountDateFormat()
    {
        $this->assertEquals(
            count(ImportMapping::$mappingDates),
            static::$countDateFormat,
            'Some date format not check, please add it into data dataProviderForDateFormat'
        );
    }
}
