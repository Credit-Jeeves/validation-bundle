<?php

namespace RentJeeves\CoreBundle\Tests\DigitalSignature;

use RentJeeves\CoreBundle\DigitalSignature\XMLSignatureManager;
use \DOMDocument;

class XMLSignatureManagerCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldSignXml()
    {
        $xmlTemplate = __DIR__.'/../Fixtures/DigitalSignature/sign_template.xml';

        $signatureManager = $this->getSignatureManager();

        $dom = new DOMDocument();
        $dom->load($xmlTemplate);

        $result = $signatureManager->sign($dom);

        $signature = $result->getElementsByTagName('Signature');

        $this->assertEquals(1, $signature->length);
        $this->assertEquals('SignedInfo', $signature->item(0)->childNodes->item(0)->localName);
        $this->assertEquals('SignatureValue', $signature->item(0)->childNodes->item(1)->localName);
        $this->assertEquals('KeyInfo', $signature->item(0)->childNodes->item(2)->localName);
    }

    /**
     * @test
     */
    public function shouldCalcCorrectDigestValue()
    {
        $xmlTemplate = __DIR__.'/../Fixtures/DigitalSignature/sign_template.xml';

        $signatureManager = $this->getSignatureManager();

        $dom = new DOMDocument();
        $dom->load($xmlTemplate);

        $result = $signatureManager->sign($dom);
        $digestValue = $result->getElementsByTagName('DigestValue')->item(0)->nodeValue;

        $this->assertEquals('lNc3oXH3XqCmtIzHm57Zwa1T9Q0=', $digestValue);
    }

    /**
     * @test
     */
    public function shouldCalcCorrectSignatureValue()
    {
        $xmlTemplate = __DIR__.'/../Fixtures/DigitalSignature/sign_template.xml';

        $signatureManager = $this->getSignatureManager();

        $dom = new DOMDocument();
        $dom->load($xmlTemplate);

        $result = $signatureManager->sign($dom);
        $signatureValue = $result->getElementsByTagName('SignatureValue')->item(0)->nodeValue;

        $expected = 'X4FZRCAGrWKsvqOYsO3dTm4rSmbrdr+XHLHDgow5z1uiR+DjXpgA5g57Kts8gD9TD7GaAKtqRo33Nvgt1igBFMJWEJjvg' .
            'XCcr/zP6Mi2LhtxBfpKWYDmT3FafGfnaKEK1HmWqqIkOC9QZ5kXh1Oc+n5ckMw3q4vr9fz6478sfv9ZKvuN3yGr/LEanVCVYLrlrtXtB' .
            'GwctB8da9Isyg+9JvtNcT4iu1fpaB9kCh8m9fosAYps8sZhi6S7xWJo43raG3DroXpDPc2d8lkAwr51TMlqSZgRKZ7vn/uFGvx3uZXb' .
            't8wvQIwKEvHQrjjIdOw9WBiwqjVvvfq03YXUAgOgTQ==';

        $this->assertEquals($expected, $signatureValue);
    }

    /**
     * @test
     */
    public function shouldUseEnvelopedAndExclusiveCanonicalTransforms()
    {
        $xmlTemplate = __DIR__.'/../Fixtures/DigitalSignature/sign_template.xml';

        $signatureManager = $this->getSignatureManager();

        $dom = new DOMDocument();
        $dom->load($xmlTemplate);

        $result = $signatureManager->sign($dom);

        $canonicalizationMethod = $result->getElementsByTagName('CanonicalizationMethod')->item(0);
        $this->assertEquals(
            'http://www.w3.org/2001/10/xml-exc-c14n#',
            $canonicalizationMethod->attributes->item(0)->nodeValue
        );

        $transforms = $result->getElementsByTagName('Transforms');
        $this->assertEquals(2, $transforms->item(0)->childNodes->length);

        $firstTransform = $transforms->item(0)->childNodes->item(0);
        $this->assertEquals(
            'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
            $firstTransform->attributes->item(0)->nodeValue
        );

        $secondTransform = $transforms->item(0)->childNodes->item(1);
        $this->assertEquals(
            'http://www.w3.org/2001/10/xml-exc-c14n#',
            $secondTransform->attributes->item(0)->nodeValue
        );
    }

    /**
     * @test
     */
    public function shouldUseRsaSha1SignAlgorithm()
    {
        $xmlTemplate = __DIR__.'/../Fixtures/DigitalSignature/sign_template.xml';

        $signatureManager = $this->getSignatureManager();

        $dom = new DOMDocument();
        $dom->load($xmlTemplate);

        $result = $signatureManager->sign($dom);

        $canonicalizationMethod = $result->getElementsByTagName('SignatureMethod')->item(0);
        $this->assertEquals(
            'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
            $canonicalizationMethod->attributes->item(0)->nodeValue
        );
    }

    /**
     * @return XMLSignatureManager
     */
    protected function getSignatureManager()
    {
        $signatureManager = new XMLSignatureManager();

        return $signatureManager;
    }
}
