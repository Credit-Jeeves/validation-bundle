<?php

namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\LandlordBundle\Registration\MerchantAccountModel;
use RentJeeves\LandlordBundle\Registration\SAMLEnvelope;
use \DOMDocument;

class SAMLEnvelopeCase extends BaseTestCase
{
    protected static $user;

    public function setUp()
    {
        if (!self::$user) {
            $this->load(true);
            //@TODO Its hack, becouse after use load function, for load fixtures, we have problem.
            static::$kernel = null;
            //end hack
            $container = static::getContainer();
            $em = $container->get('doctrine.orm.entity_manager');

            self::$user = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
                array(
                    'email' => 'landlord1@example.com',
                )
            );
        }
    }

    /**
     * @test
     */
    public function shouldCreatePortalApplicationOnConstruct()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $this->assertInstanceOf('DomDocument', $saml->getPortalApplication());
    }

    /**
     * @test
     */
    public function shouldCreateAssertionResponseOnConstruct()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $this->assertInstanceOf('DomDocument', $saml->getAssertionResponse());
    }

    /**
     * @test
     */
    public function shouldUseBase64ToEncodePortalApplication()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $expected = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPEluYm91bmRDb25maWd1cmF0aW9uPjxJbmJvdW5' .
            'kQXBwbGljYXRpb25Db25maWd1cmF0aW9ucz48SW5ib3VuZEFwcGxpY2F0aW9uQ29uZmlndXJhdGlvbj48TWVyY2hhbnREYmFOY' .
            'W1lPlRJTU9USFkgQVBQTEVHQVRFPC9NZXJjaGFudERiYU5hbWU+PE1lcmNoYW50RW1haWw+bGFuZGxvcmQxQGV4YW1wbGUuY29tP' .
            'C9NZXJjaGFudEVtYWlsPjxNZXJjaGFudFBob25lPjc4NTg2NTUzOTI8L01lcmNoYW50UGhvbmU+PENvcnBvcmF0ZU5hbWU+VElN' .
            'T1RIWSBBUFBMRUdBVEU8L0NvcnBvcmF0ZU5hbWU+PENvcnBvcmF0ZVBob25lPjc4NTg2NTUzOTI8L0NvcnBvcmF0ZVBob25lPjx' .
            'NZXJjaGFudFByaW1hcnlDb250YWN0TmFtZT5USU1PVEhZIEFQUExFR0FURTwvTWVyY2hhbnRQcmltYXJ5Q29udGFjdE5hbWU+PE' .
            '1lcmNoYW50UHJpbWFyeUNvbnRhY3RQaG9uZT43ODU4NjU1MzkyPC9NZXJjaGFudFByaW1hcnlDb250YWN0UGhvbmU+PE1lcmNo' .
            'YW50TnVtYmVyT2ZMb2NhdGlvbnM+MTwvTWVyY2hhbnROdW1iZXJPZkxvY2F0aW9ucz48TWVyY2hhbnRBZGRyZXNzPjxTdHJlZXQ+' .
            'S0lOR1NUT04gRFI8L1N0cmVldD48Q2l0eT5MQVdSRU5DRTwvQ2l0eT48WmlwPjY2MDQ5MTYxNDwvWmlwPjxVc1N0YXRlPktTPC9' .
            'Vc1N0YXRlPjwvTWVyY2hhbnRBZGRyZXNzPjxPd25lck9mZmljZXJzPjxPd25lck9mZmljZXI+PEVtYWlsQWRkcmVzcz5sYW5kbG9' .
            'yZDFAZXhhbXBsZS5jb208L0VtYWlsQWRkcmVzcz48Rmlyc3ROYW1lPlRJTU9USFk8L0ZpcnN0TmFtZT48TGFzdE5hbWU+QVBQTEV' .
            'HQVRFPC9MYXN0TmFtZT48SG9tZVBob25lPjc4NTg2NTUzOTI8L0hvbWVQaG9uZT48T3duZXJPZmZpY2VyQWRkcmVzcz48U3RyZW' .
            'V0PktJTkdTVE9OIERSPC9TdHJlZXQ+PENpdHk+TEFXUkVOQ0U8L0NpdHk+PFppcD42NjA0OTE2MTQ8L1ppcD48VXNTdGF0ZT5LUz' .
            'wvVXNTdGF0ZT48L093bmVyT2ZmaWNlckFkZHJlc3M+PC9Pd25lck9mZmljZXI+PC9Pd25lck9mZmljZXJzPjxNZXJjaGFudEFj' .
            'Y291bnQ+PE1lcmNoYW50QWNjb3VudD48QWNjb3VudE51bWJlcj48L0FjY291bnROdW1iZXI+PFRyYW5zaXRSb3V0ZXJBYmFOdW' .
            '1iZXI+PC9UcmFuc2l0Um91dGVyQWJhTnVtYmVyPjxBY2NvdW50VHlwZT48L0FjY291bnRUeXBlPjwvTWVyY2hhbnRBY2NvdW50' .
            'PjwvTWVyY2hhbnRBY2NvdW50PjwvSW5ib3VuZEFwcGxpY2F0aW9uQ29uZmlndXJhdGlvbj48L0luYm91bmRBcHBsaWNhdGlvbk' .
            'NvbmZpZ3VyYXRpb25zPjwvSW5ib3VuZENvbmZpZ3VyYXRpb24+Cg==';

        $this->assertEquals($expected, $saml->encodePortalApplication());
    }

    /**
     * @test
     */
    public function shouldUseBase64ToEncodeAssertionResponse()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('TestElement', 'Some test data inside'));

        $this->assertEquals(
            'PFRlc3RFbGVtZW50PlNvbWUgdGVzdCBkYXRhIGluc2lkZTwvVGVzdEVsZW1lbnQ+',
            $saml->encodeAssertionResponse($dom)
        );
    }

    /**
     * @test
     */
    public function shouldCreateValidPortalApplicationData()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $portalApplication = $saml->getPortalApplication();

        $this->assertTrue(
            $portalApplication->schemaValidate(__DIR__.'/../Fixtures/InboundApplicationConfiguration.xsd')
        );
    }

    /**
     * @test
     */
    public function shouldAddSuccessUrlAttributeIfItWasPassedAsAConstructorParameter()
    {
        $successUrl = 'http://rt.com/success';

        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount(), $successUrl);
        $assertionResponse = $saml->getAssertionResponse();
        $samlAttributes = $assertionResponse->getElementsByTagName('Attribute');

        $this->assertEquals(3, $samlAttributes->length);
        $this->assertEquals('successURL', $samlAttributes->item(2)->attributes->item(0)->nodeValue);
        $this->assertEquals($successUrl, $samlAttributes->item(2)->childNodes->item(0)->nodeValue);
    }

    /**
     * @test
     */
    public function shouldAddErrorUrlAttributeIfItWasPassedAsAConstructorParameter()
    {
        $successUrl = 'http://rt.com/success';
        $errorUrl = 'http://rt.com/error';

        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount(), $successUrl, $errorUrl);
        $assertionResponse = $saml->getAssertionResponse();
        $samlAttributes = $assertionResponse->getElementsByTagName('Attribute');

        $this->assertEquals(4, $samlAttributes->length);
        $this->assertEquals('successURL', $samlAttributes->item(2)->attributes->item(0)->nodeValue);
        $this->assertEquals('errorURL', $samlAttributes->item(3)->attributes->item(0)->nodeValue);
        $this->assertEquals($errorUrl, $samlAttributes->item(3)->childNodes->item(0)->nodeValue);
    }

    /**
     * @test
     */
    public function shouldNotAddErrorAndSuccessUrlAttributesIfTheyWereNotPassedAsConstructorParameters()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());
        $assertionResponse = $saml->getAssertionResponse();
        $samlAttributes = $assertionResponse->getElementsByTagName('Attribute');

        $this->assertEquals(2, $samlAttributes->length);
        $this->assertEquals('PortalApplicationSaml', $samlAttributes->item(0)->attributes->item(0)->nodeValue);
        $this->assertEquals('Email', $samlAttributes->item(1)->attributes->item(0)->nodeValue);
    }

    /**
     * @test
     */
    public function shouldAddEncodedPortalApplicationAsPortalApplicationSamlNode()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());
        $assertionResponse = $saml->getAssertionResponse();
        $portalApplicationSaml = $assertionResponse->getElementsByTagName('Attribute')->item(0)->childNodes->item(0);

        $this->assertEquals($saml->encodePortalApplication(), $portalApplicationSaml->nodeValue);
    }

    protected function getMerchantAccount()
    {
        return new MerchantAccountModel('', '', '');
    }

    protected function getUser()
    {
        return self::$user;
    }
}
