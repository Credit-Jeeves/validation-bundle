<?php
namespace RentJeeves\TenantBundle\Tests\Form\DataTransformer;

use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class PhoneNumberTransformerCase extends BaseTestCase
{
    public function transformDataProvider()
    {
        return [
            ['1251261278', '(125) 126-1278'],
            ['2225558880', '(222) 555-8880'],
        ];
    }

    public function reverseTransformDataProvider()
    {
        return [
            ['(555) 666-3330', '5556663330'],
            ['(111) 587-9876', '1115879876'],
        ];
    }

    /**
     * @test
     */
    public function shouldImplementDataTransformerInterface()
    {
        $transformer = new PhoneNumberTransformer();
        $this->assertInstanceOf('Symfony\Component\Form\DataTransformerInterface', $transformer);
    }

    /**
     * @test
     * @dataProvider transformDataProvider
     */
    public function shouldTransform10DigitsNumberToAViewWithBracketsAndDash($source, $result)
    {
        $transformer = new PhoneNumberTransformer();
        $this->assertEquals($result, $transformer->transform($source));
    }

    /**
     * @test
     * @dataProvider reverseTransformDataProvider
     */
    public function shouldReverseTransformAnyInputToOnlyDigitsView($source, $result)
    {
        $transformer = new PhoneNumberTransformer();
        $this->assertEquals($result, $transformer->reverseTransform($source));
    }
}
