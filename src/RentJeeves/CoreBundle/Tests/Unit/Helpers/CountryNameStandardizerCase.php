<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Helpers;

use RentJeeves\CoreBundle\Helpers\CountryNameStandardizer;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class CountryNameStandardizerCase extends UnitTestBase
{
    /**
     * @return array
     */
    public function dateProviderForStandardize()
    {
        return [
            ['USA', 'US'],
            ['CAN', 'CA'],
            ['CA', 'CA'],
            ['US', 'US'],
            ['Canada', 'CA'],
            ['Ukraine', 'Ukraine'],
        ];
    }

    /**
     * @param string $input
     * @param string $expectedOutput
     *
     * @test
     * @dataProvider dateProviderForStandardize
     */
    public function shouldStandardizeCountry($input, $expectedOutput)
    {
        $this->assertEquals(
            $expectedOutput,
            CountryNameStandardizer::standardize($input),
            'Not valid result for ' . $input
        );
    }
}
