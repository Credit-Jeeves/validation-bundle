<?php
namespace RentJeeves\CoreBundle\Tests\Services;

use RentJeeves\CoreBundle\Services\PhoneNumberFormatter;

class PhoneNumberFormatterCase extends \PHPUnit_Framework_TestCase
{
    public function tenDigitsProvider()
    {
        return [
            ['5556663330', '(555) 666-3330'],
            ['1115879876', '(111) 587-9876'],
        ];
    }

    public function nonTenDigitsProvider()
    {
        return [
            ['587', '587'],
            ['125jhg***', '125'],
            ['abcd', '']
        ];
    }

    /**
     * @test
     * @dataProvider tenDigitsProvider
     */
    public function shouldFormat10DigitsPhoneNumberWithBracketsAndDash($source, $expectedResult)
    {
        $formattedNumber = PhoneNumberFormatter::formatWithBracketsAndDash($source);
        $this->assertEquals($expectedResult, $formattedNumber, 'Phone number can not be formatted');
    }

    /**
     * @test
     * @dataProvider nonTenDigitsProvider
     */
    public function shouldNotFormatNon10DigitsPhoneNumber($source, $expectedResult)
    {
        $formattedNumber = PhoneNumberFormatter::formatWithBracketsAndDash($source);
        $this->assertEquals($expectedResult, $formattedNumber, 'Phone number can not be formatted');
    }

    /**
     * @test
     * @dataProvider tenDigitsProvider
     */
    public function shouldFormatPhoneNumberToOnlyDigits($expectedResult, $source)
    {
        $formattedNumber = PhoneNumberFormatter::formatToDigitsOnly($source);
        $this->assertEquals($expectedResult, $formattedNumber, 'Phone number can not be formatted');
    }
}
