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

        $expected = 'qabGaqfEbJH9MIfndn9xaHg1j+FUXIeB66LhRVGE1NZ15/ctxW7MF4RWm3E7UFEjC/LbT/ejEQj5UrROrMD/Xrea3tecn' .
            'LuBx+dj4omE7Xmb+V2yu9JTgV0jKU5iIsb8U/HOr+WgCLiVUtUluvzjRRuFGuW8iOtokPb0bC6Do+d5Ys3L7zLbElF/IICuBod0' .
            'eWEAMRAXVuT4HwsDCD44xkJPM0g95KEjHfdXUnTG4HC2+h6HwQLBg3Qv/enhUVHnpskkmJoxzPebzYFkvJ+O57E0bF80d4OBkYP' .
            'p8SOZbExKkmHMVo71IUGfdQHkbVxv9rflT80pWrE9t14b4kr55A==';

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
