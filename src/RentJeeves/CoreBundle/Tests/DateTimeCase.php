<?php
namespace RentJeeves\CoreBundle\Tests;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\CoreBundle\DateTime;

class DateTimeCase extends BaseTestCase
{
    public function providerSetDate()
    {
        $now = new DateTime();
        return array(
            array('2014-02-28', 31, 2),
            array('2014-02-01', 1, 2),
            array('2014-06-30', 31, 6),
            array($now->format('Y-m-d'), null, null, null),
        );
    }

    /**
     * @test
     * @dataProvider providerSetDate
     */
    public function setDate($result, $day, $month, $year = '2014')
    {
        $dateTime = new DateTime();
        $dateTime->setDate($year, $month, $day);
        $this->assertEquals($result, $dateTime->format('Y-m-d'));
    }

    public function providerModify()
    {
        return array(
            array('2014-02-28', '0 day 0 month 0 year'),
            array('2014-01-28', 'next month'),
            array('2014-01-28', 'next months'),
            array('2014-01-02', '+1 month', '2014-02-02'),
            array('2014-01-31', '1 months'),
            array('2014-03-31', '-1 month'),
            array('2012-02-29', '24 month'),
            array('2016-02-29', '-24 months'),
            array('2014-02-27', 'next day'),
            array('2014-02-27', '1 day'),
            array('2014-02-27', '+1 day'),
            array('2013-02-01', '-1 day next month next year'),
            array('2013-01-27', '1 day 1 month 1 year'),
            array('2015-04-01', '-1 day -1 month -1 year'),
            array('2011-12-26', '2 day 2 month 2 year'),
            array('2011-12-31', '2 month 2 year'),
        );
    }

    /**
     * @test
     * @dataProvider providerModify
     */
    public function modify($date, $shift, $result = '2014-02-28')
    {
        $dateTime = new DateTime($date);
        $dateTime->modify($shift);
        $this->assertEquals($result, $dateTime->format('Y-m-d'));
    }

    /**
     * @test
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function modifyError()
    {
        $dateTime = new DateTime();
        $dateTime->modify('2 month sdfg year');
    }
}
