<?php
namespace RentJeeves\ComponentBundle\Tests\Unit\Utility;

use RentJeeves\ComponentBundle\Utility\ShorteningUtility;
use RentJeeves\TestBundle\BaseTestCase;

class ShorteningUtilityCase extends BaseTestCase
{
    public static function dataProviderStrings()
    {
        return [
            [
                '770 Broadway, Manhattan, #2-a New York, NY 10003',
                '770 Broadway, Manhattan, #2-a New York, NY 10003',
            ],
            [
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014!' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014+' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014-' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014=' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014.',

                '360 North Arroyo Grande Boulevard, Henderson, NV 89014!' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014+' .
                '360 North Arroyo derson, NV 89014-' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014=' .
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014.',
            ],
            [
                '6535 Easter then Springs Boulevard #211 Mesa, AZ 85206',
                '6535 Easter then Springs evard #211 Mesa, AZ 85206',
                50,
            ],
            [
                '
                6535 East Superstition Springs Boulevard #211 Mesa, AZ 85206
                ',
                '6535 East Superstition Spevard #211 Mesa, AZ 85206',
                50
            ],
        ];
    }

    /**
     * @dataProvider dataProviderStrings
     * @test
     */
    public function shrink($string, $result, $length = null)
    {
        $shrinkString = $length ? ShorteningUtility::shrink($string, $length) : ShorteningUtility::shrink($string);

        $this->assertEquals($result, $shrinkString);

        $length || $length = ShorteningUtility::MAX_LENGTH;

        $this->assertTrue(mb_strlen($shrinkString) <= $length);

    }

    public static function dataProviderStringReplacing()
    {
        return [
            [
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014!',
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014!',
            ],
            [
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014!',
                'Round N Arroyo Grant Boulevard, Henderson, NV !',
                [
                    'North' => 'N',
                    '360' => 'Round',
                    '89014' => '',
                    'Grande' => 'Grant',
                    'Arr' => 'Brr'
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderStringReplacing
     * @test
     */
    public function replaceByVocabulary($string, $result, $vocabulary = [])
    {
        $replacedString = ShorteningUtility::replaceByVocabulary($string, $vocabulary);

        $this->assertEquals($result, $replacedString);
    }
}
