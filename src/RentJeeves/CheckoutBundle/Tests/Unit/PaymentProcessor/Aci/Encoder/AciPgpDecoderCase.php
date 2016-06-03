<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\Encoder;

use RentJeeves\TestBundle\BaseTestCase;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder\AciPgpDecoder;

class AciPgpDecoderCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldInstanceofRightInterface()
    {
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\FileDecoderInterface',
            $this->getAciPgpDecoder()
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage PGP key is not found
     */
    public function shouldThrowExceptionIfCreateObjectWithWrongPath()
    {
        new AciPgpDecoder('/test.txt');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciDecoderException
     */
    public function shouldThrowExceptionIfUseDecodeWithWrongParameter()
    {
        $this->getAciPgpDecoder()->decode('/test.txt');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciDecoderException
     * @expectedExceptionMessage Gnupg`s error : decrypt failed
     */
    public function shouldThrowExceptionIfUseDecodeForNotDecodedFile()
    {
        $this->getAciPgpDecoder()->decode(__DIR__ . '/../../../../Data/PaymentProcessor/Aci/Encoder/justFile.txt');
    }

    /**
     * @test
     */
    public function shouldDecodeFile()
    {
        $decodedData = $this->getAciPgpDecoder()->decode(
            __DIR__ . '/../../../../Data/PaymentProcessor/Aci/Encoder/encodedFile.pgp'
        );
        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../../../Data/PaymentProcessor/Aci/Encoder/decodedFile.csv'),
            $decodedData
        );
    }

    /**
     * @return AciPgpDecoder
     */
    protected function getAciPgpDecoder()
    {
        $path = $this->getContainer()->getParameter('aci.pgp_key_path');

        return new AciPgpDecoder($path);
    }
}
