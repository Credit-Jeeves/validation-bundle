<?php
namespace RentJeeves\ComponentBundle\Tests\Unit\Utility;

use RentJeeves\ComponentBundle\Utility\ShorteningAddressUtility;
use RentJeeves\TestBundle\BaseTestCase;

class ShorteningAddressUtilityCase extends BaseTestCase
{

    public static function dataProviderAddresses()
    {
        return [
            [
                '770 Broadway, Manhattan, #2-a New York, NY 10003',
                '770 Broadway, Manhattan, #2-a New York, NY 10003',
            ],
            [
                '360 North Arroyo Grande Boulevard, Henderson, NV 89014',
                '360 N Arroyo Grande Blvd, Henderson, NV 89014',
            ],
            [
                '6535 Easter then Springs Boulevard #211 Mesa, AZ 85206',
                '6535 Easter then Springs Blvd #211 Mesa, AZ 85206',
            ],
            [
                '6535 East Superstition Springs Boulevard #211 Mesa, AZ 85206',
                '6535 E Superstition Sprin Blvd #211 Mesa, AZ 85206',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderAddresses
     * @test
     */
    public function shrinkAddress($address, $result)
    {
        $truncatedAddress = ShorteningAddressUtility::shrinkAddress($address);

        $this->assertEquals($result, $truncatedAddress);
    }
}
