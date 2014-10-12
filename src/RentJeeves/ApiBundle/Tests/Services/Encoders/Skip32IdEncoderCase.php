<?php

namespace RentJeeves\ApiBundle\Tests\Services\Encoders;

use RentJeeves\ApiBundle\Services\Encoders\IdEncoderInterface;
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
        /** @var IdEncoderInterface $obfuscator */
        $obfuscator = $this->getContainer()->get('api.id_obfuscator');
        $encodedId = $obfuscator->encode($id);

        $this->assertEquals($encodedId, $resultId);
    }

    /**
     * @test
     * @dataProvider dataProviderIds
     */
    public function decode($resultId, $encodedId)
    {
        /** @var IdEncoderInterface $obfuscator */
        $obfuscator = $this->getContainer()->get('api.id_obfuscator');
        $id = $obfuscator->decode($encodedId);

        $this->assertEquals($id, $resultId);
    }
}
