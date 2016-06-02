<?php

namespace RentJeeves\ApiBundle\Tests\Unit\Services\Encoders;

use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\TestBundle\BaseTestCase;

class Skip32IdEncoderCase extends BaseTestCase
{
    public static function dataProviderIds()
    {
        return [
            [ 4294967295, 572455217 ],
            [ 0, 2511139177 ],
            [ 1, 656765400],
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderIds
     */
    public function encode($id, $resultId)
    {
        /** @var AttributeEncoderInterface $encoder */
        $encoder = $this->getContainer()->get('skip32.id_encoder');
        $encodedId = $encoder->encode($id);

        $this->assertEquals($encodedId, $resultId);
    }

    /**
     * @test
     * @dataProvider dataProviderIds
     */
    public function decode($resultId, $encodedId)
    {
        /** @var AttributeEncoderInterface $encoder */
        $encoder = $this->getContainer()->get('skip32.id_encoder');
        $id = $encoder->decode($encodedId);

        $this->assertEquals($id, $resultId);
    }

    /**
     * @test
     * @expectedException        \RentJeeves\ApiBundle\Services\Encoders\ValidationEncoderException
     * @expectedExceptionMessage Value "TEST" isn't correct encrypted Id.
     */
    public function exception()
    {
        $encoder = $this->getContainer()->get('skip32.id_encoder');
        $encoder->decode('TEST');
    }
}
