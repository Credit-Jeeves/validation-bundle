<?php

namespace RentJeeves\CoreBundle\Tests\Twig;

use RentJeeves\CoreBundle\Twig\Extension;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class TwigExtensionCase extends BaseTestCase
{
    /**
     * @var Extension
     */
    protected $twigExtension;

    public function setUp()
    {
        $this->twigExtension = new Extension();
    }

    public static function ordinalNumbersDataProvider()
    {
        return [
            [1, '1st'],
            [2, '2nd'],
            [3, '3rd'],
            [5, '5th'],
            ['N?A', 'N?A'],
            [20, '20th'],
            [12, '12th'],
            [15, '15th'],
            [21, '21st'],
            [32, '32nd'],
            [113, '113th'],
            [53, '53rd'],
            [66, '66th'],
        ];
    }

    /**
     * @test
     * @dataProvider ordinalNumbersDataProvider
     */
    public function ordinalNumber($number, $result)
    {
        $ordinalNumber = $this->twigExtension->ordinalNumber($number);

        $this->assertEquals($result, $ordinalNumber);
    }
}
