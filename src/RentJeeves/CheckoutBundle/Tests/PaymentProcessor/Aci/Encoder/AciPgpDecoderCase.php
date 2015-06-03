<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\Encoder;

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
        $this->getAciPgpDecoder()->decode(__DIR__ . '/../../../Fixtures/Aci/justFile.txt');
    }

    /**
     * @test
     */
    public function shouldDecodeFile()
    {
        $decodedData = $this->getAciPgpDecoder()->decode(__DIR__ . '/../../../Fixtures/Aci/encodedFile.pgp');
        $this->assertEquals(file_get_contents(__DIR__ . '/../../../Fixtures/Aci/decodedFile.csv'), $decodedData);
    }

    /**
     * @return AciPgpDecoder
     */
    protected function getAciPgpDecoder()
    {
        return $this->getContainer()->get('payment_processor.aci.pgp_decoder');
    }
}
